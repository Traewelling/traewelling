<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class ApiCheckinTest extends ApiTestCase
{
    use RefreshDatabase;

    private string $plus_one_day_then_8pm = "+1 day 8:00";

    public function setUp(): void {
        parent::setUp();
        $this->loginGertrudAndAckGDPR();
    }

    /**
     * Getting the autocomplete and only checking if the response is 200.
     *
     * @test
     */
    public function autocomplete(): void {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->get(route('api.v0.checkin.train.autocomplete', ['station' => 'Hamb']));
        $this->checkHafasException($response);
        $response->assertOk();
    }

    /**
     * Use the stationboard api and check if it works.
     * @test
     */
    public function stationboardTest(): void {
        $requestDate = Carbon::parse($this->plus_one_day_then_8pm);
        $stationname = "Frankfurt(Main)Hbf";
        $ibnr        = 8000105;
        $response    = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                            ->json('GET',
                                   route('api.v0.checkin.train.stationboard'),
                                   [
                                       'station' => $stationname,
                                       'when'    => $requestDate->toIso8601String()
                                   ]);
        $this->checkHafasException($response);
        $response->assertOk();
        $jsonResponse = json_decode($response->getContent(), true);
        $station      = $jsonResponse['station'];
        $departures   = $jsonResponse['departures'];

        // Ensure its the same station
        $this->assertEquals($stationname, $station['name']);
        $this->assertEquals($ibnr, $station['ibnr']);
        $this->assertTrue(array_reduce($departures, function($carry, $hafastrip) use ($requestDate) {
            return $carry && $this->isCorrectHafasTrip((object) $hafastrip, $requestDate);
        }, true));
    }

    /**
     * Test if the latest stations are really shown.
     * @test
     */
    public function latestStationsTest(): void {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->json('GET', route('api.v0.checkin.train.latest'));
        $response->assertOk();
    }

    /**
     * Test the home stations
     * @test
     */
    public function homeStationTest(): void {

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->json('PUT', route('api.v0.checkin.train.home'), ['ibnr' => '8000105']);
        $this->checkHafasException($response, 404);
        $response->assertOk();
        $this->assertEquals($response->getContent(), '"Frankfurt(Main)Hbf"');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->json('GET', route('api.v0.checkin.train.home'));
        $response->assertOk();
        $response->assertJsonStructure(['id', 'ibnr', 'name', 'latitude', 'longitude']);
        $station = json_decode($response->getContent(), true)['name'];
        $this->assertEquals($station, "Frankfurt(Main)Hbf");
    }
}
