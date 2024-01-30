<?php

namespace Tests\Feature;

use App\Exceptions\HafasException;
use App\Http\Controllers\Backend\Transport\StationController;
use App\Http\Controllers\HafasController;
use App\Models\Station;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function testStringSearch(): void {
        $searchResults = [self::HANNOVER_HBF];
        Http::fake(["*" => Http::response($searchResults)]);

        $station = StationController::lookupStation(self::HANNOVER_HBF['name']);
        $this->assertEquals(self::HANNOVER_HBF['name'], $station->name);
    }

    public function testNameNotFound(): void {
        Http::fake(["*" => Http::response([], 200)]);

        $this->assertThrows(function() {
            StationController::lookupStation("Bielefeld Hbf");
        }, ModelNotFoundException::class);
    }

    public function testDs100Search(): void {
        Http::fake(["*/stations/" . self::HANNOVER_HBF['ril100'] => Http::response(self::HANNOVER_HBF)]);

        $station = StationController::lookupStation(self::HANNOVER_HBF['ril100']);
        $this->assertEquals(self::HANNOVER_HBF['name'], $station->name);
    }

    public function testDs100NotFound(): void {
        Http::fake(["*" => Http::response([], 200)]);

        $this->assertThrows(function() {
            StationController::lookupStation("EBIL");
        }, ModelNotFoundException::class);
    }

    public function testIbnrLocalSearch(): void {
        Http::preventStrayRequests();
        $expected = Station::factory()->make();
        $expected->save();

        $station = StationController::lookupStation(str($expected->ibnr));
        $this->assertEquals(Station::find($expected->id)->name, $station->name);
    }

    public function testGetNearbyStations(): void {
        Http::fake(["*/stops/nearby*" => Http::response([array_merge(
                                                             self::HANNOVER_HBF,
                                                             ["distance" => 421]
                                                         )])]);

        $result = HafasController::getNearbyStations(
            self::HANNOVER_HBF['location']['latitude'],
            self::HANNOVER_HBF['location']['longitude']);

        $this->assertEquals(self::HANNOVER_HBF['name'], $result[0]->name);
        $this->assertEquals(421, $result[0]->distance,);
    }

    public function testGetNearbyStationFails(): void {
        Http::fake(Http::response(status: 503));

        $this->assertThrows(function() {
            HafasController::getNearbyStations(
                self::HANNOVER_HBF['location']['latitude'],
                self::HANNOVER_HBF['location']['longitude']);
        }, HafasException::class);
    }
}
