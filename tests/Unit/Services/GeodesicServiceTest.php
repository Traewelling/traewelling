<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dto\Coordinate;
use App\Services\GeodesicService;
use Tests\TestCase;

class GeodesicServiceTest extends TestCase
{
    private GeodesicService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeodesicService();
    }

    public function test_interpolate_returns_requested_number_of_points(): void
    {
        $from = new Coordinate(52.5200, 13.4050); // Berlin
        $to = new Coordinate(48.8566, 2.3522);    // Paris

        $points = $this->service->interpolate($from, $to, 50);

        $this->assertCount(50, $points);
    }

    public function test_interpolate_starts_and_ends_at_given_coordinates(): void
    {
        $from = new Coordinate(52.5200, 13.4050); // Berlin
        $to = new Coordinate(48.8566, 2.3522);    // Paris

        $points = $this->service->interpolate($from, $to, 100);

        $this->assertEqualsWithDelta($from->latitude, $points[0]->latitude, 1e-6);
        $this->assertEqualsWithDelta($from->longitude, $points[0]->longitude, 1e-6);
        $this->assertEqualsWithDelta($to->latitude, $points[99]->latitude, 1e-6);
        $this->assertEqualsWithDelta($to->longitude, $points[99]->longitude, 1e-6);
    }

    public function test_interpolate_with_single_point_returns_start_coordinate(): void
    {
        $from = new Coordinate(52.5200, 13.4050);
        $to = new Coordinate(48.8566, 2.3522);

        $points = $this->service->interpolate($from, $to, 1);

        $this->assertCount(1, $points);
        $this->assertEqualsWithDelta($from->latitude, $points[0]->latitude, 1e-6);
        $this->assertEqualsWithDelta($from->longitude, $points[0]->longitude, 1e-6);
    }

    public function test_interpolate_identical_points_returns_same_coordinate(): void
    {
        $from = new Coordinate(52.5200, 13.4050);

        $points = $this->service->interpolate($from, $from, 10);

        $this->assertCount(10, $points);
        foreach ($points as $point) {
            $this->assertEqualsWithDelta($from->latitude, $point->latitude, 1e-6);
            $this->assertEqualsWithDelta($from->longitude, $point->longitude, 1e-6);
        }
    }

    public function test_haversine_distance_between_berlin_and_paris_is_approximately_878km(): void
    {
        $berlin = new Coordinate(52.5200, 13.4050);
        $paris = new Coordinate(48.8566, 2.3522);

        $distanceInMeters = $this->service->haversineDistance($berlin, $paris);

        // Known great-circle distance Berlin-Paris: approx. 878 km, allow +-5 km tolerance.
        $this->assertEqualsWithDelta(878_000, $distanceInMeters, 5_000);
    }

    public function test_haversine_distance_between_identical_points_is_zero(): void
    {
        $coord = new Coordinate(52.5200, 13.4050);

        $distance = $this->service->haversineDistance($coord, $coord);

        $this->assertSame(0, $distance);
    }

    public function test_haversine_distance_is_symmetric(): void
    {
        $berlin = new Coordinate(52.5200, 13.4050);
        $paris = new Coordinate(48.8566, 2.3522);

        $this->assertSame(
            $this->service->haversineDistance($berlin, $paris),
            $this->service->haversineDistance($paris, $berlin),
        );
    }

    public function test_find_nearest_point_index_returns_the_closest_point(): void
    {
        $path = [
            new Coordinate(52.0, 13.0),
            new Coordinate(52.1, 13.0),
            new Coordinate(52.2, 13.0),
        ];

        $this->assertSame(1, $this->service->findNearestPointIndex(new Coordinate(52.11, 13.0), $path));
    }

    public function test_find_nearest_point_index_never_looks_before_the_given_index(): void
    {
        // A line that returns to where it started: without the cursor, the second visit would
        // snap back onto the first one and any slice taken from it would run backwards.
        $path = [
            new Coordinate(52.0, 13.0),
            new Coordinate(52.5, 13.0),
            new Coordinate(52.01, 13.0),
        ];

        $needle = new Coordinate(52.0, 13.0);

        $this->assertSame(0, $this->service->findNearestPointIndex($needle, $path));
        $this->assertSame(2, $this->service->findNearestPointIndex($needle, $path, 1));
    }

    public function test_find_nearest_point_index_is_null_when_nothing_is_left_to_search(): void
    {
        $path = [new Coordinate(52.0, 13.0)];

        $this->assertNull($this->service->findNearestPointIndex(new Coordinate(52.0, 13.0), $path, 1));
        $this->assertNull($this->service->findNearestPointIndex(new Coordinate(52.0, 13.0), []));
    }

    public function test_path_length_sums_up_all_segments(): void
    {
        $berlin = new Coordinate(52.5200, 13.4050);
        $paris = new Coordinate(48.8566, 2.3522);
        $path = $this->service->interpolate($berlin, $paris, 100);

        $length = $this->service->pathLength($path);

        // Following the great circle in 100 steps must match the direct distance closely.
        $this->assertEqualsWithDelta($this->service->haversineDistance($berlin, $paris), $length, 1_000);
    }

    public function test_path_length_of_a_single_point_is_zero(): void
    {
        $this->assertSame(0.0, $this->service->pathLength([new Coordinate(52.5200, 13.4050)]));
        $this->assertSame(0.0, $this->service->pathLength([]));
    }

    public function test_interpolated_points_lie_on_the_great_circle(): void
    {
        // Verify that the midpoint of Berlin-Paris lies at the expected great-circle latitude.
        $berlin = new Coordinate(52.5200, 13.4050);
        $paris = new Coordinate(48.8566, 2.3522);

        $points = $this->service->interpolate($berlin, $paris, 3);
        $midpoint = $points[1];

        // The midpoint should be north of Paris and south of Berlin.
        $this->assertGreaterThan($paris->latitude, $midpoint->latitude);
        $this->assertLessThan($berlin->latitude, $midpoint->latitude);

        // The midpoint longitude should be between Berlin and Paris.
        $this->assertGreaterThan($paris->longitude, $midpoint->longitude);
        $this->assertLessThan($berlin->longitude, $midpoint->longitude);
    }
}
