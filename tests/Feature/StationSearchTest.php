<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\Transport\StationController;
use App\Models\TrainStation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function testStringSearch(): void {
        $station = StationController::lookupStation('Hannover Hbf');
        $this->assertEquals('Hannover Hbf', $station->name);
    }

    public function testDs100Search(): void {
        $station = StationController::lookupStation('HH');
        $this->assertEquals('Hannover Hbf', $station->name);
    }

    public function testIdSearch(): void {
        $station = StationController::lookupStation(1);
        $this->assertEquals(TrainStation::find(1)->name, $station->name);
    }
}
