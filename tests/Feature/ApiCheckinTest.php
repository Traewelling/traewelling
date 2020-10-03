<?php

namespace Tests\Feature;

use DateTime;
use Tests\ApiTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiCheckinTest extends ApiTestCase
{
    use RefreshDatabase;

    private $plus_one_day_then_8pm = "+1 day 8:00";

    public function setUp(): void {
        parent::setUp();
        $this->loginGertrudAndAckGDPR();
    }

    /**
     * Getting the autocomplete and only checking if the response is 200.
     *
     * @test
     */
    public function autocomplete() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get(route('api.v0.checkin.train.autocomplete', ['station' => 'Hamb']));
        $response->assertOk();
    }

    /**
     * Use the stationboard api and check if it works.
     * @test
     */
    public function stationboardTest() {
        $requestDate = new DateTime($this->plus_one_day_then_8pm);
        $stationname = "Frankfurt(Main)Hbf";
        $ibnr        = 8000105;
        $response    = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('GET',
                   route('api.v0.checkin.train.stationboard'),
                   ['station' => $stationname, 'when' => $requestDate->format('U')]);
        $response->assertOk();
        $jsonResponse = json_decode($response->getContent(), true);
        $station      = $jsonResponse['station'];
        $departures   = $jsonResponse['departures'];



        // Ensure its the same station
        $this->assertEquals($stationname, $station['name']);
        $this->assertEquals($ibnr, $station['id']);
        $this->assertTrue(array_reduce($departures, function($carry, $hafastrip) use ($requestDate) {
            return $carry && $this->isCorrectHafasTrip((object) $hafastrip, $requestDate);
        }, true));
    }


    /**
     * Testing the checkin blah blah blah
     *
     * @test
     */
    public function testCheckin() {
        // First: Get a train
        $now         = new DateTime($this->plus_one_day_then_8pm);
        $stationname = "Frankfurt(Main)Hbf";
        $response    = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('GET', route('api.v0.checkin.train.stationboard'), ['station' => $stationname,
                'when' => $now->format('U')]);

        $trainStationboard = json_decode($response->getContent(), true);
        $countDepartures   = count($trainStationboard['departures']);
        if($countDepartures == 0) {
            $this->markTestSkipped("Unable to find matching trains. Is it night in $stationname?");
            return;
        }

        // Second: We don't like broken or cancelled trains.
        $i = 0;
        while ((isset($trainStationboard['departures'][$i]['cancelled'])
                && $trainStationboard['departures'][$i]['cancelled'])
            || count($trainStationboard['departures'][$i]['remarks']) != 0
        ) {
            $i++;
            if ($i == $countDepartures) {
                $this->markTestSkipped("Unable to find unbroken train. Is it stormy in $stationname?");
                return;
            }
        }
        $departure = $trainStationboard['departures'][$i];
        $this->isCorrectHafasTrip((object) $departure, $now);

        // Third: Get the trip information for train
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('GET', route('api.v0.checkin.train.trip'), ['tripID' => $departure['tripId'],
                'lineName' => $departure['line']['name'], 'start' => $departure['stop']['location']['id']]);

        $trip = json_decode($response->getContent(), true);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('POST', route('api.v0.checkin.train.checkin'), ['tripID' => $departure['tripId'],
                 'start' => (string) $departure['stop']['location']['id'],
                 'destination' => $trip['stopovers'][0]['stop']['location']['id'],
                'body' => 'Example Body']);
        $response->assertOk();
        $response->assertJsonStructure([
            'distance',
            'duration',
            'points',
            'lineName',
            'alsoOnThisConnection'
        ]);
    }

    /**
     * Test if the latest stations are really shown.
     * @test
     */
    public function latestStationsTest() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('GET', route('api.v0.checkin.train.latest'));
        $response->assertOk();
    }

    /**
     * Test the home stations
     * @test
     */
    public function homeStationTest() {

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('PUT', route('api.v0.checkin.train.home'), ['ibnr' => '8000105']);
        $response->assertOk();
        $this->assertTrue($response->getContent() == '"Frankfurt(Main)Hbf"');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('GET', route('api.v0.checkin.train.home'));
        $response->assertOk();
        $response->assertJsonStructure(['id', 'ibnr', 'name', 'latitude', 'longitude']);
        $station = json_decode($response->getContent(), true)['name'];
        $this->assertTrue($station == "Frankfurt(Main)Hbf");
    }
}
