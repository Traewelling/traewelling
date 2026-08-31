<?php

namespace Tests\Feature;

use App\Enum\HafasTravelType;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Models\Checkin;
use App\Models\PolyLine;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\FeatureTestCase;
use Traewelling\GooglePolyline\PolylineTranscoder;

class LocationControllerCalculateDistanceTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_calculate_distance_with_simple_stopovers(): void
    {
        $origin = Station::factory(['latitude' => 52.379811, 'longitude' => 9.742779])->create();
        $destination = Station::factory(['latitude' => 52.341994, 'longitude' => 9.718319])->create();

        $trip = Trip::create([
            'trip_id' => '1|2|3|4',
            'category' => HafasTravelType::REGIONAL,
            'number' => 'xxx',
            'linename' => 'xxx',
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'departure' => Date::now()->subHour(),
            'arrival' => Date::now()->addHour(),
        ]);

        $originStopover = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $origin->id,
            'departure_planned' => Date::now(),
        ])->create();

        $destinationStopover = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $destination->id,
            'arrival_planned' => Date::now()->addHour(),
        ])->create();

        $trip->load(['stopovers']);

        $result = (new LocationController($trip, $originStopover, $destinationStopover))->calculateDistance();

        $this->assertEquals(4526, $result);
    }

    public function test_calculate_distance_with_only_one_segment(): void
    {
        $station1 = Station::factory(['latitude' => 52.379811, 'longitude' => 9.742779])->create();
        $station2 = Station::factory(['latitude' => 52.341994, 'longitude' => 9.718319])->create();
        $station3 = Station::factory(['latitude' => 52.360000, 'longitude' => 9.730000])->create();

        $trip = Trip::create([
            'trip_id' => '1|2|3|4',
            'category' => HafasTravelType::REGIONAL,
            'number' => 'xxx',
            'linename' => 'xxx',
            'origin_id' => $station1->id,
            'destination_id' => $station2->id,
            'departure' => Date::now()->subHour(),
            'arrival' => Date::now()->addHour(),
        ]);

        $routeSegment2 = RouteSegment::factory([
            'from_station_id' => $station2->id,
            'to_station_id' => $station3->id,
            'distance' => 3000,
        ])->create();

        $stopover1 = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $station1->id,
            'departure_planned' => Date::now(),
            'arrival_planned' => null,
        ])->create();

        $stopover2 = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $station2->id,
            'departure_planned' => Date::now()->addHour(),
            'route_segment_id' => $routeSegment2->id,
        ])->create();
        $stopover3 = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $station3->id,
            'arrival_planned' => Date::now()->addHours(2),
            'departure_planned' => null,
        ])->create();

        $trip->load(['stopovers']);

        $result = (new LocationController($trip, $stopover1, $stopover3))->calculateDistance();

        $this->assertEquals(7526, $result);

        $result = (new LocationController($trip, $stopover1, $stopover2))->calculateDistance();
        $this->assertEquals(4526, $result);
    }

    public function test_calculate_distance_with_all_route_segment(): void
    {
        $station1 = Station::factory(['latitude' => 52.379811, 'longitude' => 9.742779])->create();
        $station2 = Station::factory(['latitude' => 52.341994, 'longitude' => 9.718319])->create();
        $station3 = Station::factory(['latitude' => 52.360000, 'longitude' => 9.730000])->create();

        $trip = Trip::create([
            'trip_id' => '1|2|3|4',
            'category' => HafasTravelType::REGIONAL,
            'number' => 'xxx',
            'linename' => 'xxx',
            'origin_id' => $station1->id,
            'destination_id' => $station2->id,
            'departure' => Date::now()->subHour(),
            'arrival' => Date::now()->addHour(),
        ]);

        $routeSegment = RouteSegment::factory([
            'from_station_id' => $station1->id,
            'to_station_id' => $station2->id,
            'distance' => 5000,
        ])->create();
        $routeSegment2 = RouteSegment::factory([
            'from_station_id' => $station2->id,
            'to_station_id' => $station3->id,
            'distance' => 3000,
        ])->create();

        $stopover1 = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $station1->id,
            'departure_planned' => Date::now(),
            'arrival_planned' => null,
            'route_segment_id' => $routeSegment->id,
        ])->create();

        $stopover2 = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $station2->id,
            'departure_planned' => Date::now()->addHour(),
            'route_segment_id' => $routeSegment2->id,
        ])->create();
        $stopover3 = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $station3->id,
            'arrival_planned' => Date::now()->addHours(2),
            'departure_planned' => null,
        ])->create();

        $trip->load(['stopovers']);

        $result = (new LocationController($trip, $stopover1, $stopover3))->calculateDistance();

        $this->assertEquals(8000, $result);

        $result = new LocationController($trip, $stopover1, $stopover2)->calculateDistance();
        $this->assertEquals(5000, $result);
    }

    public function test_calculate_distance_with_polyline(): void
    {
        $origin = Station::factory(['latitude' => 50.637486, 'longitude' => 3.071129])->create();
        $destination = Station::factory(['latitude' => 48.880886, 'longitude' => 2.354931])->create();

        $geoJson = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => ['stationId' => $origin->id, 'departure_planned' => Date::now()->toIso8601ZuluString()],
                    'geometry' => ['type' => 'Point', 'coordinates' => [3.071129, 50.637486]],
                ],
                [
                    'type' => 'Feature',
                    'properties' => [],
                    'geometry' => ['type' => 'Point', 'coordinates' => [3.0, 50.0]],
                ],
                [
                    'type' => 'Feature',
                    'properties' => ['stationId' => $destination->id, 'arrival_planned' => Date::now()->addHour()->toIso8601ZuluString()],
                    'geometry' => ['type' => 'Point', 'coordinates' => [2.354931, 48.880886]],
                ],
            ],
        ];

        $polyline = PolyLine::factory(['polyline' => json_encode($geoJson)])->create();

        $trip = Trip::create([
            'trip_id' => '1|2|3|4',
            'category' => HafasTravelType::REGIONAL,
            'number' => 'xxx',
            'linename' => 'xxx',
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'departure' => Date::now()->subHour(),
            'arrival' => Date::now()->addHour(),
            'polyline_id' => $polyline->id,
        ]);

        $originStopover = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $origin->id,
            'departure_planned' => Date::now(),
        ])->create();

        $destinationStopover = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $destination->id,
            'arrival_planned' => Date::now()->addHour(),
        ])->create();

        $trip->load(['stopovers', 'polyline']);

        $result = (new LocationController($trip, $originStopover, $destinationStopover))->calculateDistance();

        $this->assertGreaterThan(0, $result);
    }

    public function test_calculate_distance_with_multiple_intermediate_stopovers_without_route_segments(): void
    {
        $station1 = Station::factory(['latitude' => 52.379811, 'longitude' => 9.742779])->create();
        $station2 = Station::factory(['latitude' => 52.360000, 'longitude' => 9.730000])->create();
        $station3 = Station::factory(['latitude' => 52.341994, 'longitude' => 9.718319])->create();

        $trip = Trip::create([
            'trip_id' => '1|2|3|4',
            'category' => HafasTravelType::REGIONAL,
            'number' => 'xxx',
            'linename' => 'xxx',
            'origin_id' => $station1->id,
            'destination_id' => $station3->id,
            'departure' => Date::now()->subHour(),
            'arrival' => Date::now()->addHours(2),
        ]);

        $stopover1 = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $station1->id,
            'departure_planned' => Date::now(),
        ])->create();

        $stopover2 = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $station2->id,
            'arrival_planned' => Date::now()->addHour(),
            'departure_planned' => Date::now()->addHour(),
        ])->create();

        $stopover3 = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $station3->id,
            'arrival_planned' => Date::now()->addHours(2),
        ])->create();

        $trip->load(['stopovers']);

        $result = (new LocationController($trip, $stopover1, $stopover3))->calculateDistance();

        $this->assertGreaterThan(0, $result);
    }

    public function test_calculate_distance_fallback_to_stopovers_when_polyline_too_short(): void
    {
        $origin = Station::factory(['latitude' => 52.379811, 'longitude' => 9.742779])->create();
        $destination = Station::factory(['latitude' => 52.341994, 'longitude' => 9.718319])->create();

        $polyline = PolyLine::factory(['polyline' => '{}'])->create();

        $trip = Trip::create([
            'trip_id' => '1|2|3|4',
            'category' => HafasTravelType::REGIONAL,
            'number' => 'xxx',
            'linename' => 'xxx',
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'departure' => Date::now()->subHour(),
            'arrival' => Date::now()->addHour(),
            'polyline_id' => $polyline->id,
        ]);

        $originStopover = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $origin->id,
            'departure_planned' => Date::now(),
        ])->create();

        $destinationStopover = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $destination->id,
            'arrival_planned' => Date::now()->addHour(),
        ])->create();

        $trip->load(['stopovers', 'polyline']);

        $result = (new LocationController($trip, $originStopover, $destinationStopover))->calculateDistance();

        $this->assertEquals(4526, $result);
    }

    /**
     * Two route segment points less than a metre apart used to push the spherical law of cosines
     * above 1, so acos() returned NAN and the int-typed distance sum threw an uncaught TypeError,
     * which took down the whole /api/v1/positions response instead of just this one status.
     */
    public function test_calculate_live_position_with_sub_metre_polyline_points(): void
    {
        $origin = Station::factory(['latitude' => 51.517477, 'longitude' => 7.687345])->create();
        $destination = Station::factory(['latitude' => 51.500000, 'longitude' => 7.600000])->create();

        $departure = Date::now()->subMinutes(10);
        $arrival = Date::now()->addMinutes(10);

        // Second point is the same latitude, one step of 1e-6 degrees (about 7 cm) to the west.
        $polyline = new PolylineTranscoder()->encodePolyline([
            [7.687345, 51.517477],
            [7.687344, 51.517477],
            [7.600000, 51.500000],
        ], 6);

        $routeSegment = RouteSegment::factory([
            'from_station_id' => $origin->id,
            'to_station_id' => $destination->id,
            'polyline' => $polyline,
            'polyline_precision' => 6,
        ])->create();

        $trip = Trip::create([
            'trip_id' => '1|2|3|4',
            'category' => HafasTravelType::REGIONAL,
            'number' => 'xxx',
            'linename' => 'xxx',
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'departure' => $departure,
            'arrival' => $arrival,
        ]);

        $originStopover = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $origin->id,
            'arrival_planned' => $departure,
            'departure_planned' => $departure,
            'route_segment_id' => $routeSegment->id,
        ])->create();

        $destinationStopover = Stopover::factory([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $destination->id,
            'arrival_planned' => $arrival,
            'departure_planned' => $arrival,
        ])->create();

        $trip->load(['stopovers', 'polyline']);

        $checkin = Checkin::factory([
            'trip_id' => $trip->trip_id,
            'origin_stopover_id' => $originStopover->id,
            'destination_stopover_id' => $destinationStopover->id,
            'departure' => $departure,
            'arrival' => $arrival,
        ])->create();

        $position = LocationController::forStatus($checkin->status->fresh())->calculateLivePosition();

        $this->assertNotNull($position);
    }
}
