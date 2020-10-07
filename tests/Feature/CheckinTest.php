<?php

namespace Tests\Feature;

use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\User;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Transport\Transport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\TransportController;

class CheckinTest extends TestCase
{

    use RefreshDatabase;

    private $plus_one_day_then_8pm = "+1 day 8:00";

    /**
     * Use the stationboard api and check if it works.
     * @test
     */
    public function stationboardTest() {
        $requestDate       = new DateTime($this->plus_one_day_then_8pm);
        $stationname       = "Frankfurt(Main)Hbf";
        $ibnr              = 8000105; // This station has departures throughout the night.
        $trainStationboard = TransportController::TrainStationboard($stationname, $requestDate->format('U'));
        $station           = $trainStationboard['station'];
        $departures        = $trainStationboard['departures'];

        // Ensure its the same station
        $this->assertEquals($stationname, $station['name']);
        $this->assertEquals($ibnr, $station['id']);

        // Analyse the stationboard departures
        // This is just a very long construct to ensure that each and every hafas trip is reported
        // correctly. I'm using this over a loop with single assertions so there is a consistent
        // amount of assertions, no matter what time how the trains are moving.
        $this->assertTrue(array_reduce($departures, function($carry, $hafastrip) use ($requestDate) {
            return $carry && $this->isCorrectHafasTrip($hafastrip, $requestDate);
        }, true));

    }


    /**
     * The nearby endpoint should redirect the user to the
     *
     * @test
     */
    public function stationboardByLocationPositiveTest()
    {
        // GIVEN: A logged-in and gdpr-acked user
        $user     = User::factory()->create();
        $response = $this->actingAs($user)
            ->post('/gdpr-ack');
        $response->assertStatus(302);
        $response->assertRedirect('/');

        // GIVEN: A bunch of locations around Europe that should return true
        $locations = [
            //["name" => "Dortmund Hbf", "station" => "Hauptbahnhof, Dortmund", "latitude" => 51.517, "longitude" => 7.4592],
            ["name" => "FRA", "station" => "Frankfurt(M) Flughafen Fernbf", "latitude" => 50.052926, "longitude" => 8.569776],
            //["name" => "Moskau", "station" => "Moskva Oktiabrskaia", "latitude" => 55.776111, "longitude" => 37.655278]
        ];

        foreach ($locations as $testcase) {
            // WHEN: Requesting the stationboard based on Coordinates
            $response = $this->actingAs($user)
                ->get(route("trains.nearby", [
                    "latitude" => $testcase["latitude"],
                    "longitude" => $testcase["longitude"]
                ]));

            // THEN: Expect the redirect to another stationboard
            $response->assertStatus(302);
            $response->assertRedirect(route("trains.stationboard", [
                'station' => $testcase["station"],
                'provider' => 'train'
            ]));
        }
    }

    /**
     * @test
     */
    public function stationboardByLocationNegativeTest()
    {
        // GIVEN: A logged-in and gdpr-acked user
        $user     = User::factory()->create();
        $response = $this->actingAs($user)
            ->post('/gdpr-ack');
        $response->assertStatus(302);
        $response->assertRedirect('/');

        // GIVEN: A bunch of Locations
        $locations = [
            ["name" => "Null Island", "latitude" => 0, "longitude" => 0],
            ["name" => "New York City", "latitude" => 40.730610, "longitude" => -73.935242]
        ];

        foreach ($locations as $testcase) {
            // WHEN: Requesting the stationboard based on Coordinates
            $response = $this->actingAs($user)
                ->get(route("trains.nearby", [
                    "latitude" => $testcase["latitude"],
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
    public function testCheckin() {
        // First: Get a train that's fine for our stuff
        $now               = new DateTime($this->plus_one_day_then_8pm);
        $stationname       = "Frankfurt(M) Flughafen Fernbf";
        $ibnr              = "8070003";
        $trainStationboard = TransportController::TrainStationboard($stationname, $now->format('U'), 'express');

        $countDepartures = count($trainStationboard['departures']);
        if($countDepartures == 0) {
            $this->markTestSkipped("Unable to find matching trains. Is it night in $stationname?");
            return;
        }

        // Second: We don't like broken or cancelled trains.
        $i = 0;
        while ((isset($trainStationboard['departures'][$i]->cancelled)
                && $trainStationboard['departures'][$i]->cancelled)
              || count($trainStationboard['departures'][$i]->remarks) != 0) {
            $i++;
            if ($i == $countDepartures) {
                $this->markTestSkipped("Unable to find unbroken train. Is it stormy in $stationname?");
                return;
            }
        }
        $departure = $trainStationboard['departures'][$i];
        $this->isCorrectHafasTrip($departure, $now);

        // Third: Get the trip information
        $trip = TransportController::TrainTrip(
            $departure->tripId,
            $departure->line->name,
            $departure->stop->location->id
        );

        // GIVEN: A logged-in and gdpr-acked user
        $user     = User::factory()->create();
        $response = $this->actingAs($user)
                         ->post('/gdpr-ack');
        $response->assertStatus(302);
        $response->assertRedirect('/');

        // WHEN: User tries to check-in
        $response = $this->actingAs($user)
                ->post(route('trains.checkin'), [
                    'body' => 'Example Body',
                    'tripID' => $departure->tripId,
                    'start' => $ibnr,
                    'destination' => $trip['stopovers'][0]['stop']['location']['id'],
                ]);

        // THEN: The user is redirected to dashboard and flashes the linename.
        $response->assertStatus(302);
        $response->assertSessionHas('checkin-success.lineName', $departure->line->name);

        // THEN: The user (just created earlier in the method) has one status.
        $statuses = $user->statuses->all();
        $this->assertCount(1, $statuses);

        // THEN: You can get the status page and see its information
        $response = $this->get(url('/status/' . $statuses[0]->id));
        $response->assertOk();
        $response->assertSee($stationname, false); // Departure Station
        $response->assertSee($trip['stopovers'][0]['stop']['name'], false); // Arrival Station
    }
    /*
     * Test if the checkin collision is truly working
     * @test
     */
    public function testCheckinCollision() {
        // GIVEN: Generate TrainStations
        TrainStation::factory()->count(4)->create();

        // GIVEN: A logged-in and gdpr-acked user
        $user     = User::factory()->create();
        $response = $this->actingAs($user)
                         ->post('/gdpr-ack');
        $response->assertStatus(302);
        $response->assertRedirect('/');

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
        $trips[]  = [];
        $trips[0] = HafasTrip::factory()->create(['departure' => date('Y-m-d H:i:s', strtotime('today 12:00')),
                                                  'arrival'   => date('Y-m-d H:i:s', strtotime('13:00'))]);
        $trips[1] = HafasTrip::factory()->create(['departure' => date('Y-m-d H:i:s', strtotime('11:45')),
                                                  'arrival'   => date('Y-m-d H:i:s', strtotime('12:15'))]);
        $trips[2] = HafasTrip::factory()->create(['departure' => date('Y-m-d H:i:s', strtotime('12:45')),
                                                  'arrival'   => date('Y-m-d H:i:s', strtotime('13:15'))]);
        $trips[3] = HafasTrip::factory()->create(['departure' => date('Y-m-d H:i:s', strtotime('12:15')),
                                                  'arrival'   => date('Y-m-d H:i:s', strtotime('12:45'))]);
        $trips[4] = HafasTrip::factory()->create(['departure' => date('Y-m-d H:i:s', strtotime('11:45')),
                                                  'arrival'   => date('Y-m-d H:i:s', strtotime('13:15'))]);
        $trips[5] = HafasTrip::factory()->create(['departure' => date('Y-m-d H:i:s', strtotime('11:15')),
                                                  'arrival'   => date('Y-m-d H:i:s', strtotime('11:45'))]);
        $trips[6] = HafasTrip::factory()->create(['departure' => date('Y-m-d H:i:s', strtotime('13:30')),
                                                  'arrival'   => date('Y-m-d H:i:s', strtotime('13:45'))]);
        //this seems to be correct. But in the database it's suddenly not correct. Idk...
        //dd($trips[0]);

        TransportController::TrainCheckin(
            $trips[0]->trip_id,
            $trips[0]->origin,
            $trips[0]->destination,
            '',
            $user,
            0,
            0,
            0,
            0
        );

        for($i = 1; $i < 5; $i++) {
            try {
                TransportController::TrainCheckin(
                    $trips[$i]->trip_id,
                    $trips[$i]->origin,
                    $trips[$i]->destination,
                    '',
                    $user,
                    0,
                    0,
                    0,
                    0
                );
                $this->fail("Expected exception for Collision Case $i not thrown");
            }catch(CheckInCollisionException $e) {
                $this->assertEquals($trips[0]->linename, $e->getCollision()->HafasTrip->first()->linename);
            }
        }

        //check normal checkin possibility
        for($i = 5; $i < 7; $i++) {
            try {
                TransportController::TrainCheckin(
                    $trips[$i]->trip_id,
                    $trips[$i]->origin,
                    $trips[$i]->destination,
                    '',
                    $user,
                    0,
                    0,
                    0,
                    0
                );
                $this->assertTrue(true);
            }catch(CheckInCollisionException $e) {
                $this->assertEquals($trips[0]->linename, $e->getCollision()->HafasTrip->first()->linename);
                $this->fail("Exception for Case $i thrown even though checkin should happen.");
            }
        }
    }

    /**
     * Let us see if the message-flash works as intended. Therfore we fake a checkin or at least
     * what the dashboard get's to see of it. We expect the dashboard to return 200OK, show the
     * checkin-data and the rest of the interface (Here: the stationboard-autocomplete and one of
     * the footer sentences).
     * @test
     */
    public function testCheckinSuccessFlash() {
        // GIVEN: A gdpr-acked user
        $user     = User::factory()->create();
        $response = $this->actingAs($user)
                         ->post('/gdpr-ack');
        $response->assertStatus(302);
        $response->assertRedirect('/');

        // WHEN: Coming back from the checkin flow and returning to the dashboard
        $message  = [
            "distance" => 72.096,
            "duration" => 1860,
            "points" => 18.0,
            "lineName" => "ICE 107",
            "alsoOnThisConnection" => new Collection(),
            "event" => null
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
}
