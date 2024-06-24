<?php

namespace Tests\Feature;

use App\Dto\Coordinate;
use App\Enum\HafasTravelType;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use App\Objects\LineSegment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\FeatureTestCase;

class DistanceCalculationTest extends FeatureTestCase
{

    use RefreshDatabase;

    public function test_distance_calculation_between_hanover_and_karlsruhe(): void {
        $result = new LineSegment(
            new Coordinate(52.376589, 9.741083),
            new Coordinate(48.993962, 8.401107)
        );
        $this->assertEquals(388213, $result->calculateDistance());
    }

    public function test_distance_calculation_between_hanover_hbf_and_hanover_kroepcke() {
        $result = new LineSegment(
            new Coordinate(52.376589, 9.741083),
            new Coordinate(52.374497, 9.738573)
        );
        $this->assertEquals(289, $result->calculateDistance());
    }

    public function test_distance_calculation_between_simple_stopovers() {
        $origin      = Station::factory([
                                            'latitude'  => 52.379811,
                                            'longitude' => 9.742779,
                                        ])->create();
        $destination = Station::factory([
                                            'latitude'  => 52.341994,
                                            'longitude' => 9.718319,
                                        ])->create();

        $trip = Trip::create([ //Don't use factory here, so the trip can be created manually here
                               'trip_id'        => '1|2|3|4',
                               'category'       => HafasTravelType::REGIONAL,
                               'number'         => 'xxx',
                               'linename'       => 'xxx',
                               'origin_id'      => $origin->id,
                               'destination_id' => $destination->id,
                               'departure'      => Date::now()->subHour(),
                               'arrival'        => Date::now()->addHour(),
                             ]);

        $originStopover      = Stopover::factory([
                                                     'trip_id'           => $trip->trip_id,
                                                     'train_station_id'  => $origin->id,
                                                     'departure_planned' => Date::now()->toIso8601String(),
                                                 ])->create();
        $destinationStopover = Stopover::factory([
                                                     'trip_id'          => $trip->trip_id,
                                                     'train_station_id' => $destination->id,
                                                     'arrival_planned'  => Date::now()
                                                                               ->addHours(1)
                                                                               ->toIso8601String(),
                                                 ])->create();

        $trip->load(['stopovers']);

        $result = (new LocationController($trip, $originStopover, $destinationStopover))->calculateDistance();
        $this->assertEquals(4526, $result);
    }

    public function test_distance_calculation_for_foreign_trip_with_stopovers(): void {
        $origin      = Station::factory([
                                            'id'        => 8700030,
                                            'name'      => 'Lille Flandres',
                                            'latitude'  => 50.637486,
                                            'longitude' => 3.071129,
                                        ])->create();
        $destination = Station::factory([
                                            'id'        => 8700014,
                                            'name'      => 'Paris Nord',
                                            'latitude'  => 48.880886,
                                            'longitude' => 2.354931,
                                        ])->create();


        $trip = Trip::create([
                                 'trip_id'        => '1|2|3|4',
                                 'category'       => HafasTravelType::REGIONAL,
                                 'number'         => 'xxx',
                                 'linename'       => 'xxx',
                                 'origin_id'      => $origin->id,
                                 'destination_id' => $destination->id,
                                 'departure'      => Date::now()->subHour(),
                                 'arrival'        => Date::now()->addHour(),
                             ]);

        $originStopover      = Stopover::factory([
                                                     'trip_id'           => $trip->trip_id,
                                                     'train_station_id'  => $origin->id,
                                                     'departure_planned' => Date::now()->subHour(),
                                                 ])->create();
        $destinationStopover = Stopover::factory([
                                                     'trip_id'          => $trip->trip_id,
                                                     'train_station_id' => $destination->id,
                                                     'arrival_planned'  => Date::now()->addHour(),
                                                 ])->create();

        $trip->load(['stopovers']);

        $result = (new LocationController($trip, $originStopover, $destinationStopover))->calculateDistance();
        $this->assertEquals(202210, $result);
    }
}
