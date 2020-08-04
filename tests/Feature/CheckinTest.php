<?php

namespace Tests\Feature;

use App\User;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
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
        $user     = factory(User::class)->create();
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
        $user     = factory(User::class)->create();
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
        $user     = factory(User::class)->create();
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
        $response->assertSee($stationname); // Departure Station
        $response->assertSee($trip['stopovers'][0]['stop']['name']); // Arrival Station
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
        $user     = factory(User::class)->create();
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
        ));

        // Usual Dashboard stuff
        $response->assertSee(__('stationboard.where-are-you'));
        $response->assertSee(__('menu.developed'));
    }
}
