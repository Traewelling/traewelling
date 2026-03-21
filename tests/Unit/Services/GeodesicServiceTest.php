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
