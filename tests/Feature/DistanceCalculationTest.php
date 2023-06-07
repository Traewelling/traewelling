<?php

namespace Tests\Feature;

use App\Enum\HafasTravelType;
use App\Http\Controllers\Backend\GeoController;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class DistanceCalculationTest extends TestCase
{

    use RefreshDatabase;

    public function test_distance_calculation_between_hanover_and_karlsruhe(): void {
        $result = GeoController::calculateDistanceBetweenCoordinates(
            latitudeA:  52.376589,
            longitudeA: 9.741083,
            latitudeB:  48.993962,
            longitudeB: 8.401107,
        );
        $this->assertEquals(388213, $result);
    }

    public function test_distance_calculation_between_hanover_hbf_and_hanover_kroepcke() {
        $result = GeoController::calculateDistanceBetweenCoordinates(
            latitudeA:  52.376589,
            longitudeA: 9.741083,
            latitudeB:  52.374497,
            longitudeB: 9.738573,
        );
        $this->assertEquals(289, $result);
    }

    public function test_distance_calculation_between_simple_stopovers() {
        $origin      = TrainStation::factory([
                                                 'latitude'  => 52.379811,
                                                 'longitude' => 9.742779,
                                             ])->create();
        $destination = TrainStation::factory([
                                                 'latitude'  => 52.341994,
                                                 'longitude' => 9.718319,
                                             ])->create();

        $hafasTrip = HafasTrip::create([ //Don't use factory here, so the trip can be created manually here
                                         'trip_id'     => '1|2|3|4',
                                         'category'    => HafasTravelType::REGIONAL,
                                         'number'      => 'xxx',
                                         'linename'    => 'xxx',
                                         'origin'      => $origin->ibnr,
                                         'destination' => $destination->ibnr,
                                         'departure'   => Date::now()->subHour(),
                                         'arrival'     => Date::now()->addHour(),
                                       ]);

        $originStopover      = TrainStopover::factory([
                                                          'trip_id'           => $hafasTrip->trip_id,
                                                          'train_station_id'  => $origin->id,
                                                          'departure_planned' => Date::now()->toIso8601String(),
                                                      ])->create();
        $destinationStopover = TrainStopover::factory([
                                                          'trip_id'          => $hafasTrip->trip_id,
                                                          'train_station_id' => $destination->id,
                                                          'arrival_planned'  => Date::now()
                                                                                      ->addHours(1)
                                                                                      ->toIso8601String(),
                                                      ])->create();

        $hafasTrip->load(['stopoversNEW']);

        $result = GeoController::calculateDistance($hafasTrip, $originStopover, $destinationStopover);
        $this->assertEquals(4526, $result);
    }

    public function test_distance_calculation_for_foreign_trip_with_stopovers(): void {
        $origin      = TrainStation::factory([
                                                 'id'        => 8700030,
                                                 'name'      => 'Lille Flandres',
                                                 'latitude'  => 50.637486,
                                                 'longitude' => 3.071129,
                                             ])->create();
        $destination = TrainStation::factory([
                                                 'id'        => 8700014,
                                                 'name'      => 'Paris Nord',
                                                 'latitude'  => 48.880886,
                                                 'longitude' => 2.354931,
                                             ])->create();


        $hafasTrip = HafasTrip::create([
                                           'trip_id'     => '1|2|3|4',
                                           'category'    => HafasTravelType::REGIONAL,
                                           'number'      => 'xxx',
                                           'linename'    => 'xxx',
                                           'origin'      => $origin->ibnr,
                                           'destination' => $destination->ibnr,
                                           'departure'   => Date::now()->subHour(),
                                           'arrival'     => Date::now()->addHour(),
                                       ]);

        $originStopover      = TrainStopover::factory([
                                                          'trip_id'           => $hafasTrip->trip_id,
                                                          'train_station_id'  => $origin->id,
                                                          'departure_planned' => Date::now()->subHour(),
                                                      ])->create();
        $destinationStopover = TrainStopover::factory([
                                                          'trip_id'          => $hafasTrip->trip_id,
                                                          'train_station_id' => $destination->id,
                                                          'arrival_planned'  => Date::now()->addHour(),
                                                      ])->create();

        $hafasTrip->load(['stopoversNEW']);

        $result = GeoController::calculateDistance($hafasTrip, $originStopover, $destinationStopover);
        $this->assertEquals(202210, $result);
    }
}
