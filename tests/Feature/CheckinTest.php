<?php

namespace Tests\Feature;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\TransportController;
use App\Models\HafasTrip;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
    }

    private $plus_one_day_then_8pm = "+1 day 8:00";

    /**
     * Use the stationboard api and check if it works.
     * @test
     */
    public function stationboardTest(): void {
        $requestDate = Carbon::parse($this->plus_one_day_then_8pm);
        $stationName = "Frankfurt(Main)Hbf";
        $ibnr        = 8000105; // This station has departures throughout the night.
        try {
            $trainStationboard = TransportController::getDepartures(
                stationQuery: $stationName,
                when:         $requestDate
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }
        $station    = $trainStationboard['station'];
        $departures = $trainStationboard['departures'];

        // Ensure its the same station
        $this->assertEquals($stationName, $station['name']);
        $this->assertEquals($ibnr, $station['ibnr']);

        // Analyse the stationboard departures
        // This is just a very long construct to ensure that each and every hafas trip is reported
        // correctly. I'm using this over a loop with single assertions so there is a consistent
        // amount of assertions, no matter what time how the trains are moving.
        $this->assertTrue(array_reduce($departures->toArray(), function($carry, $hafastrip) use ($requestDate) {
            return $carry && $this->isCorrectHafasTrip($hafastrip, $requestDate);
        },                             true));
    }

    /**
     * The nearby endpoint should redirect the user to the
     *
     * @test
     */
    public function stationboardByLocationPositiveTest(): void {
        // GIVEN: A logged-in and gdpr-acked user
        $user = $this->createGDPRAckedUser();

        // GIVEN: A bunch of locations around Europe that should return true
        $locations = [
            /*[
                "name"      => "Dortmund Hbf",
                "station"   => "Hauptbahnhof, Dortmund",
                "latitude"  => 51.517,
                "longitude" => 7.4592
            ], */
            [
                'name'      => 'FRA',
                'station'   => 8070003,
                'latitude'  => 50.052926,
                'longitude' => 8.569776
            ]
            /*, [
                "name"      => "Moskau",
                "station"   => "Moskva Oktiabrskaia",
                "latitude"  => 55.776111,
                "longitude" => 37.655278
            ]*/
        ];

        foreach ($locations as $testcase) {
            // WHEN: Requesting the stationboard based on Coordinates
            $response = $this->actingAs($user)
                             ->get(route('trains.nearby', [
                                 'latitude'  => $testcase['latitude'],
                                 'longitude' => $testcase['longitude']
                             ]));

            // THEN: Expect the redirect to another stationboard
            $response->assertStatus(302);
            $response->assertRedirect(route('trains.stationboard', [
                'station'  => $testcase['station'],
                'provider' => 'train',
            ]));
        }
    }

    /**
     * @test
     */
    public function stationboardByLocationNegativeTest(): void {
        // GIVEN: A logged-in and gdpr-acked user
        $user = $this->createGDPRAckedUser();

        // GIVEN: A bunch of Locations
        $locations = [
            ["name" => "Null Island", "latitude" => 0, "longitude" => 0],
            ["name" => "New York City", "latitude" => 40.730610, "longitude" => -73.935242]
        ];

        foreach ($locations as $testcase) {
            // WHEN: Requesting the stationboard based on Coordinates
            $response = $this->actingAs($user)
                             ->get(route("trains.nearby", [
                                 "latitude"  => $testcase["latitude"],
                                 "longitude" => $testcase["longitude"]
                             ]));

            // THEN: Expect an error
            $response->assertStatus(302);
            $response->assertSessionHas("error");
        }
    }

    /**
     * This is a lengthy test which does a lot of this and touches many endpoints. FIRST, it will
     * find an ICE train that leaving Frankfurt/Main Airport at 10:00 the next day. This way, we
     * can try to get "okayish" trains (cancels are not published this far in the future, in most
     * cases anyway). SECOND, if there is no train that suits us, because of night or because every
     * single train has a problem (storm or something), this test is skipped.
     * As the THIRD preperation, we try to receive some trip information so we can find a stop to
     * drive to.
     *
     * Now to the real GWT-Test:
     * GIVEN there's a logged-in and gdpr-acked user
     * WHEN They check into the train
     * THEN They get flashed with information about the check-in
     * THEN The user has one status noted.
     * THEN You can see the status information.
     * @test
     */
    public function testCheckin(): void {
        // First: Get a train that's fine for our stuff
        $timestamp   = Carbon::parse($this->plus_one_day_then_8pm);
        $stationName = "Frankfurt(M) Flughafen Fernbf";
        $ibnr        = "8070003";
        try {
            $trainStationboard = TransportController::getDepartures(
                stationQuery: $stationName,
                when:         $timestamp,
                travelType:   TravelType::EXPRESS
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $countDepartures = count($trainStationboard['departures']);
        if ($countDepartures === 0) {
            $this->markTestSkipped("Unable to find matching trains. Is it night in $stationName?");
        }

        // Second: We don't like broken or cancelled trains.
        $i = 0;
        while ((isset($trainStationboard['departures'][$i]->cancelled)
                && $trainStationboard['departures'][$i]->cancelled)
               || count($trainStationboard['departures'][$i]->remarks) != 0) {
            $i++;
            if ($i == $countDepartures) {
                $this->markTestSkipped("Unable to find unbroken train. Is it stormy in $stationName?");
            }
        }
        $departure = $trainStationboard['departures'][$i];
        self::isCorrectHafasTrip($departure, $timestamp);

        try {
            // Third: Get the trip information
            $trip = TransportController::TrainTrip(
                $departure->tripId,
                $departure->line->name,
                $departure->stop->location->id
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        // GIVEN: A logged-in and gdpr-acked user
        $user = $this->createGDPRAckedUser();

        // WHEN: User tries to check-in
        $response = $this->actingAs($user)
                         ->post(route('trains.checkin'), [
                             'body'              => 'Example Body',
                             'tripID'            => $departure->tripId,
                             'start'             => $ibnr,
                             'destination'       => $trip['stopovers'][0]['stop']['location']['id'],
                             'departure'         => Carbon::parse($departure->plannedWhen)->toIso8601String(),
                             'arrival'           => Carbon::parse($trip['stopovers'][0]['plannedArrival'])->toIso8601String(),
                             'checkinVisibility' => StatusVisibility::PUBLIC->value,
                             'business_check'    => Business::PRIVATE->value,
                         ]);

        // THEN: The user is redirected to dashboard and flashes the linename.
        $response->assertStatus(302);
        $response->assertSessionHas('checkin-success.lineName', $departure->line->name);

        // THEN: The user (just created earlier in the method) has one status.
        $this->assertCount(1, $user->statuses);
        $status = $user->statuses->first();

        // THEN: You can get the status page and see its information
        $response = $this->get(url('/status/' . $status->id));
        $response->assertOk();
        $response->assertSee($stationName, false);                          // Departure Station
        $response->assertSee($trip['stopovers'][0]['stop']['name'], false); // Arrival Station
        $response->assertSee("Example Body");

        $this->assertStringContainsString("Example Body (@ ", $status->socialText);
    }

    /*
     * Test if the checkin collision is truly working
     * @test
     */
    public function testCheckinCollision(): void {
        // GIVEN: Generate TrainStations
        TrainStation::factory()->count(4)->create();

        // GIVEN: A logged-in and gdpr-acked user
        $user = $this->createGDPRAckedUser();

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
        $user = $this->createGDPRAckedUser();

        // WHEN: Coming back from the checkin flow and returning to the dashboard
        $message  = [
            "distance"             => 72.096,
            "duration"             => 1860,
            "points"               => 18.0,
            "lineName"             => "ICE 107",
            "alsoOnThisConnection" => new Collection(),
            "event"                => null
        ];
        $response = $this->actingAs($user)
                         ->withSession(["checkin-success" => $message])
                         ->followingRedirects()
                         ->get(route('dashboard'));

        // THEN: The dashboard returns.
        $response->assertOk();

        // With the checkin data
        $response->assertSee(trans_choice(
                                 'controller.transport.checkin-ok',
                                 preg_match('/\s/', $message['lineName']),
                                 ['lineName' => $message['lineName']]
                             ), false);

        // Usual Dashboard stuff
        $response->assertSee(__('stationboard.where-are-you'), false);
        $response->assertSee(__('menu.developed'), false);
    }

    /**
     * Testing checkins where the line forms a ring structure (e.g. Potsdams 603 Bus). Previously,
     * TRWL produced negative trip durations, or unexpected route distances.
     *
     * @throws HafasException
     * @see    https://github.com/Traewelling/traewelling/issues/37
     * @author jeyemwey
     */
    public function testCheckinAtBus603Potsdam(): void {
        // First: Get a train that's fine for our stuff
        $timestamp   = Carbon::parse("+1 days 10:00");
        $stationName = "Schloss Cecilienhof, Potsdam";
        try {
            $trainStationboard = TransportController::getDepartures(
                stationQuery: $stationName,
                when:         $timestamp,
                travelType:   TravelType::BUS
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $countDepartures = count($trainStationboard['departures']);
        if ($countDepartures === 0) {
            $this->markTestSkipped("Unable to find matching bus. Is it night in $stationName?");
        }

        // The bus runs in a 20min interval
        $departure = $trainStationboard['departures'][0];
        self::isCorrectHafasTrip($departure, $timestamp);

        // Third: Get the trip information
        try {
            $trip = TransportController::TrainTrip(
                $departure->tripId,
                $departure->line->name,
                $departure->stop->location->id
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        // GIVEN: A logged-in and gdpr-acked user
        $user = $this->createGDPRAckedUser();

        // WHEN: User tries to check-in
        $response = $this->actingAs($user)
                         ->post(route('trains.checkin'), [
                             'body'              => 'Example Body',
                             'tripID'            => $departure->tripId,
                             // Höhenstr ist die nächste Haltestelle hinter Schloss Cecilienhof. Dort steigen wir ein
                             'start'             => $trip['stopovers'][0]['stop']['id'],
                             'departure'         => $trip['stopovers'][0]['departure'],
                             // Reiterweg ist 6 Stationen hinter Schloss Cecilienhof
                             'destination'       => $trip['stopovers'][5]['stop']['id'], // Reiterweg
                             'arrival'           => $trip['stopovers'][5]['arrival'],
                             'checkinVisibility' => "0",
                             'business_check'    => 0
                         ]);

        $response->assertStatus(302);
        $response->assertSessionHas('checkin-success.lineName', $departure->line->name);

        $checkin = TrainCheckin::first();
        // Es wird tatsächlich die zeitlich spätere Station angenommen.
        $this->assertTrue($checkin->arrival > $checkin->departure);
    }

    /**
     * Testing checkins where the line forms a ring structure, e.g. Berlins Ringbahn. The API
     * represents the Ringbahn as a fluid double-ring. If you choose to check-in at Westkreuz in a
     * counter-clockwise driving train, the API will give you the last ring (Südkreuz -
     * Gesundbrunnen - Westkreuz) and the following ring up to Südkreuz (Westkreuz - Südkreuz -
     * Gesundbrunnen - Westkreuz - Südkreuz). If you choose to get into the second ring (e.g. exit
     * at Tempelhof), TRWL has assumed, you meant the Tempelhof the first time it appeared which
     * was negative in time from our trip. This led to negative durations.
     *
     * @author jeyemwey
     * @see    https://github.com/Traewelling/traewelling/issues/37
     */
    public function testCheckinAtBerlinRingbahnRollingOverSuedkreuz(): void {
        // First: Get a train that's fine for our stuff
        // The 10:00 train actually quits at Südkreuz, but the 10:05 does not.
        $timestamp   = Carbon::parse("+1 days 10:03");
        $stationName = "Messe Nord / ICC, Berlin";
        try {
            $trainStationboard = TransportController::getDepartures(
                stationQuery: $stationName,
                when:         Carbon::parse('+1 days 10:00'),
                travelType:   TravelType::SUBURBAN,
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $countDepartures = count($trainStationboard['departures']);
        if ($countDepartures === 0) {
            $this->markTestSkipped("Unable to find matching train.");
        }

        $i         = 0;
        $departure = [];
        while ($i < $countDepartures) {
            $departure = $trainStationboard['departures'][$i];
            // We're searching for a counter-clockwise running train which are labeled "S 42"
            if ($departure->line->name === "S 42") {
                break;
            }
            $i++; // Maybe the next one?
            if ($i === $countDepartures) {
                $this->markTestSkipped("Berlins Ringbahn only runs in one direction right now.");
            }
        }

        self::isCorrectHafasTrip($departure, $timestamp);

        // Third: Get the trip information
        try {
            $trip = TransportController::TrainTrip(
                $departure->tripId,
                $departure->line->name,
                $departure->stop->location->id
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        // GIVEN: A logged-in and gdpr-acked user
        $user = User::factory(['privacy_ack_at' => Carbon::yesterday()])->create();
        dump($trip['hafasTrip']->stopoversNew->first()->trainStation->ibnr);
        dump($trip['hafasTrip']->stopoversNew->first()->departure_planned->toIso8601String());
        dump($trip['hafasTrip']->stopoversNew->last()->trainStation->ibnr);
        dump($trip['hafasTrip']->stopoversNew->last()->departure_planned->toIso8601String());


        // WHEN: User tries to check-in
        $response = $this->actingAs($user)
                         ->post(route('trains.checkin'), [
                             'body'              => 'Example Body',
                             'tripID'            => $departure->tripId,
                             // Westkreuz is right behind Messe Nord / ICC. We hop in there.
                             'start'             => $trip['hafasTrip']->stopoversNew->first()->trainStation->ibnr,
                             'departure'         => $trip['hafasTrip']->stopoversNew->first()->departure_planned->toIso8601String(),
                             // Tempelhof is 7 stations behind Westkreuz and runs over the Südkreuz mark
                             'destination'       => $trip['hafasTrip']->stopoversNew->last()->trainStation->ibnr, // Tempelhof
                             'arrival'           => $trip['hafasTrip']->stopoversNew->last()->departure_planned->toIso8601String(),
                             'checkinVisibility' => StatusVisibility::PUBLIC->value,
                             'business_check'    => Business::PRIVATE->value,
                         ]);

        $response->assertStatus(302);
        $response->assertSessionHas('checkin-success.lineName', $departure->line->name);

        $checkin = $user->statuses->first()->trainCheckin;
        // Es wird tatsächlich die zeitlich spätere Station angenommen.
        $this->assertTrue($checkin->arrival > $checkin->departure);
    }
}
