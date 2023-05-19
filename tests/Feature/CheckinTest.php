<?php

namespace Tests\Feature;

use App\Dto\CheckinSuccess;
use App\Enum\Business;
use App\Enum\PointReason;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\TransportController;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckinTest extends TestCase
{

    use RefreshDatabase;

    private string $plus_one_day_then_8pm = "+1 day 8:00";

    /**
     * Use the stationboard api and check if it works.
     * @test
     */
    public function stationboardTest(): void {
        Http::fake([
                       '/locations*'                => Http::response([self::FRANKFURT_HBF]),
                       '/stops/8000105/departures*' => Http::response([self::ICE802])
                   ]);

        $requestDate = Carbon::parse(self::DEPARTURE_TIME);

        $trainStationboard = TransportController::getDepartures(
            stationQuery: self::FRANKFURT_HBF['name'],
            when:         $requestDate
        );

        $departures = $trainStationboard['departures'];

        $this->assertCount(1, $departures);
        $this->assertEquals(self::TRIP_ID, $departures[0]->tripId);
    }

    /**
     * The nearby endpoint should redirect the user to the stationboard of the nearest station.
     *
     * @test
     */
    public function stationboardByLocationPositiveTest(): void {
        // GIVEN: A logged-in and gdpr-acked user
        $user = User::factory()->create();

        // GIVEN: A HTTP Mock
        Http::fake(["*/stops/nearby*" => Http::response([array_merge(
                                                             self::HANNOVER_HBF,
                                                             ["distance" => 421]
                                                         )])]);

        // WHEN: Requesting the stationboard based on Coordinates
        $response = $this->actingAs($user)
                         ->get(route('trains.nearby', [
                             'latitude'  => self::HANNOVER_HBF['location']['latitude'],
                             'longitude' => self::HANNOVER_HBF['location']['longitude']
                         ]));

        // THEN: Expect the redirect to another stationboard
        $response->assertStatus(302);
        $response->assertRedirect(route('trains.stationboard', [
            'station'  => self::HANNOVER_HBF['id'],
            'provider' => 'train',
        ]));
    }

    /**
     * @test
     */
    public function stationboardByLocationNegativeTest(): void {
        // GIVEN: A logged-in and gdpr-acked user
        $user = User::factory()->create();

        // GIVEN: A HTTP Mock
        Http::fake(Http::response([]));

        // WHEN: Requesting the stationboard based on Coordinates
        $response = $this->actingAs($user)
                         ->get(route('trains.nearby', [
                             'latitude'  => 0,
                             'longitude' => 0,
                         ]));

        // THEN: Expect an error
        $response->assertStatus(302);
        $response->assertSessionHas("error");
    }

    /**
     * This is a lengthy test which goes through a number of endpoints to prepare our database for a new check-in.
     * First, the test finds and persists a train in Frankfurt Hbf.
     * Then, the details of the route are persisted.
     * Finally, a check-in happens which is checked on a number of places.
     *
     * Since all data that is persisted and checked against, is mock and does not come from the real db-rest, we
     * have no issues on changes of the schedule or rainy days.
     *
     * @test
     */
    public function testCheckin(): void {
        // GIVEN: A logged-in and gdpr-acked user
        $user = User::factory()->create();

        // WHEN: User follows Check-In Flow (checks departures, takes a look at trip information, performs check-in)
        Http::fake([
                       '/locations*'                              => Http::response([self::FRANKFURT_HBF]),
                       '/stops/8000105/departures*'               => Http::response([self::ICE802]),
                       '/trips/' . urlencode(self::TRIP_ID) . '*' => Http::response(self::TRIP_INFO),
                   ]);
        TransportController::getDepartures(
            stationQuery: self::FRANKFURT_HBF['name'],
            when:         Carbon::parse(self::DEPARTURE_TIME),
            travelType:   TravelType::EXPRESS
        );

        TrainCheckinController::getHafasTrip(
            tripId:   self::TRIP_ID,
            lineName: self::ICE802['line']['name'],
            startId:  self::FRANKFURT_HBF['id']
        );

        $response = $this->actingAs($user)
                         ->post(route('trains.checkin'), [
                             'body'              => self::EXAMPLE_BODY,
                             'tripID'            => self::TRIP_ID,
                             'start'             => self::FRANKFURT_HBF['id'],
                             'departure'         => self::DEPARTURE_TIME,
                             'destination'       => self::HANNOVER_HBF['id'],
                             'arrival'           => self::ARRIVAL_TIME,
                             'checkinVisibility' => StatusVisibility::PUBLIC->value,
                             'business_check'    => Business::PRIVATE->value,
                         ]);

        // THEN: The user is redirected to dashboard and flashes the linename.
        $response->assertStatus(302);
        $response->assertSessionHas('checkin-success', function($data) {
            return $data->lineName === self::ICE802['line']['name'];
        });

        // THEN: The user has one status.
        $this->assertCount(1, $user->statuses);
        $status = $user->statuses->first();

        // THEN: You can get the status page and see its information
        $response = $this->get(url('/status/' . $status->id));
        $response->assertOk();
        $response->assertSee(self::FRANKFURT_HBF['name'], false); // Departure Station
        $response->assertSee(self::HANNOVER_HBF['name'], false);  // Arrival Station
        $response->assertSee(self::EXAMPLE_BODY);

        $this->assertStringContainsString(self::EXAMPLE_BODY . " (@ ", $status->socialText);
    }

    /**
     * Test if the checkin collision is truly working
     * @test
     */
    public function testCheckinCollision(): void {
        // GIVEN: Generate TrainStations
        TrainStation::factory()->count(4)->create();

        // GIVEN: A logged-in and gdpr-acked user
        $user = User::factory()->create();

        /*
         * We're now generating a 'base checkin' on which we are comparing all possible collision types
         *                 |   |   | 12:00 |   |   | 13:00 |   |   |   |
         *    Base:        |   |   |   |░░░░░░░░░░░░░░░|   |   |   |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 1:      |  11:45|▓▓▓▓▓▓▓|12:15  |   |   |   |   |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 2:      |   |   |   |   |  12:45|▓▓▓▓▓▓▓|13:15  |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 3:      |   |   |  12:15|▓▓▓▓▓▓▓|12:45  |   |   |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 4:      |  11:45|▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓|13:15  |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 5: 11:15|▓▓▓▓▓▓▓|11:45  |   |   |   |   |   |   |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 6:      |   |   |   |   |   |   |   |  13:30|▓▓▓▓▓▓▓|13:45
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *
         */

        $collisionTrips    = [];
        $nonCollisionTrips = [];
        $baseTrip          = HafasTrip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:s', strtotime('12:00')),
                'arrival'   => date('Y-m-d H:i:s', strtotime('13:00'))
            ]
        );

        //Trips Case 1 - 4 for which a collisionException should be thrown
        $collisionTrips[] = HafasTrip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:s', strtotime('11:45')),
                'arrival'   => date('Y-m-d H:i:s', strtotime('12:15'))
            ]
        );
        $collisionTrips[] = HafasTrip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:s', strtotime('12:45')),
                'arrival'   => date('Y-m-d H:i:s', strtotime('13:15'))
            ]
        );
        $collisionTrips[] = HafasTrip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:s', strtotime('12:15')),
                'arrival'   => date('Y-m-d H:i:s', strtotime('12:45'))
            ]
        );
        $collisionTrips[] = HafasTrip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:s', strtotime('11:45')),
                'arrival'   => date('Y-m-d H:i:s', strtotime('13:15'))
            ]
        );

        //Trips case 5 & 6 for which no Exception should be thrown
        $nonCollisionTrips[] = HafasTrip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:s', strtotime('11:15')),
                'arrival'   => date('Y-m-d H:i:s', strtotime('11:45'))
            ]
        );
        $nonCollisionTrips[] = HafasTrip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:s', strtotime('13:30')),
                'arrival'   => date('Y-m-d H:i:s', strtotime('13:45'))
            ]
        );

        try {
            TrainCheckinController::checkin(
                user:        $user,
                hafasTrip:   $baseTrip,
                origin:      $baseTrip->originStation,
                departure:   $baseTrip->departure,
                destination: $baseTrip->destinationStation,
                arrival:     $baseTrip->arrival,
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $caseCount = 1; //This variable is needed to output error messages in case of a failed test
        foreach ($collisionTrips as $trip) {
            try {
                TrainCheckinController::checkin(
                    user:        $user,
                    hafasTrip:   $trip,
                    origin:      $trip->originStation,
                    departure:   $trip->departure,
                    destination: $trip->destinationStation,
                    arrival:     $trip->arrival,
                );
                $this->fail("Expected exception for Collision Case $caseCount not thrown");
            } catch (CheckInCollisionException $exception) {
                $this->assertEquals($baseTrip->linename, $exception->getCollision()->HafasTrip->first()->linename);
            } catch (HafasException $e) {
                $this->markTestSkipped($e->getMessage());
            }
            $caseCount++;
        }

        //check normal checkin possibility
        foreach ($nonCollisionTrips as $trip) {
            try {
                TrainCheckinController::checkin(
                    user:        $user,
                    hafasTrip:   $trip,
                    origin:      $trip->originStation,
                    departure:   $trip->departure,
                    destination: $trip->destinationStation,
                    arrival:     $trip->arrival,
                );
                $this->assertTrue(true);
            } catch (CheckInCollisionException $exception) {
                $this->assertEquals($baseTrip->linename, $exception->getCollision()->HafasTrip->first()->linename);
                $this->fail("Exception for Case $caseCount thrown even though checkin should happen.");
            } catch (HafasException $e) {
                $this->markTestSkipped($e->getMessage());
            }
            $caseCount++;
        }
    }

    /**
     * Let us see if the message-flash works as intended. Therfore we fake a checkin or at least
     * what the dashboard get's to see of it. We expect the dashboard to return 200OK, show the
     * checkin-data and the rest of the interface (Here: the stationboard-autocomplete and one of
     * the footer sentences).
     */
    public function testCheckinSuccessFlash(): void {
        // GIVEN: A gdpr-acked user
        $user = User::factory()->create();

        // WHEN: Coming back from the checkin flow and returning to the dashboard
        $dto  = new CheckinSuccess(
            id:                   1,
            distance:             72.096,
            duration:             1860,
            points:               18,
            pointReason:          PointReason::IN_TIME,
            lineName:             "ICE 107",
            socialText:           "example share text",
            alsoOnThisConnection: new Collection(),
            event:                null,
        );
        $response = $this->actingAs($user)
                         ->withSession(["checkin-success" => $dto])
                         ->followingRedirects()
                         ->get(route('dashboard'));

        // THEN: The dashboard returns.
        $response->assertOk();

        // With the checkin data
        $response->assertSee(trans_choice(
                                 'controller.transport.checkin-ok',
                                 preg_match('/\s/', $dto->lineName),
                                 ['lineName' => $dto->lineName]
                             ));

        // Usual Dashboard stuff
        $response->assertSee(__('stationboard.where-are-you'), false);
        $response->assertSee(__('menu.developed'), false);
    }
}
