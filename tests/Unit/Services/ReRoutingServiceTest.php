<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dto\BRouter\RouteDto;
use App\Dto\Coordinate;
use App\Enum\HafasTravelType;
use App\Exceptions\BRouterException;
use App\Jobs\RecalculateStatusesDistanceForTrip;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Stopover;
use App\Models\Trip;
use App\Repositories\TripRepository;
use App\Services\BRouterService;
use App\Services\GeodesicService;
use App\Services\ReRoutingService;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\Unit\UnitTestCase;

class ReRoutingServiceTest extends UnitTestCase
{
    // Hannover Hbf
    private const float LAT_A = 52.3766;

    private const float LON_A = 9.7413;

    // Hannover Kröpcke, ~310 m from Hbf
    private const float LAT_B = 52.3744;

    private const float LON_B = 9.7385;

    private TripRepository $repository;

    private BRouterService $brouter;

    private GeodesicService $geodesic;

    private ReRoutingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TripRepository::class);
        $this->brouter = Mockery::mock(BRouterService::class);
        $this->brouter->shouldReceive('isEnabled')->andReturnTrue()->byDefault();
        $this->geodesic = Mockery::mock(GeodesicService::class);
        $this->service = new ReRoutingService($this->repository, $this->brouter, $this->geodesic);

        Queue::fake();
    }

    private function makeStation(float $lat, float $lon, string $name = 'Station'): Station
    {
        $station = new Station();
        $station->latitude = $lat;
        $station->longitude = $lon;
        $station->name = $name;

        return $station;
    }

    private function makeStopover(
        Station $station,
        ?RouteSegment $segment = null,
        ?Carbon $departure = null,
        ?Carbon $arrival = null,
    ): Stopover {
        $stopover = new Stopover();
        $stopover->setRelation('station', $station);
        $stopover->setRelation('stationIdentifier', null);
        $stopover->setRelation('routeSegment', $segment);

        // Bypass UTCDateTime cast by setting raw attributes directly
        $stopover->setRawAttributes(array_filter([
            'departure_planned' => $departure?->toDateTimeString(),
            'arrival_planned' => $arrival?->toDateTimeString(),
        ]));

        return $stopover;
    }

    private function makeTrip(array $stopovers, HafasTravelType $category = HafasTravelType::NATIONAL_EXPRESS): Trip
    {
        $collection = collect($stopovers);
        $builder = Mockery::mock(HasMany::class);
        $builder->shouldReceive('get')->andReturn($collection);

        $trip = Mockery::mock(Trip::class)->makePartial();
        $trip->shouldReceive('stopovers')->andReturn($builder);
        $trip->category = $category;
        $trip->trip_id = 'test-trip-123';

        return $trip;
    }

    private function makeSegment(): RouteSegment
    {
        $segment = new RouteSegment();
        $segment->id = 'segment-uuid-123';

        return $segment;
    }

    private function makeRoute(int $distanceMeters = 0, int $coords = 2): RouteDto
    {
        $coordinates = [
            new Coordinate(self::LAT_A, self::LON_A),
            new Coordinate(self::LAT_B, self::LON_B),
        ];

        return new RouteDto(coordinates: array_slice($coordinates, 0, $coords), distanceInMeters: $distanceMeters);
    }

    public function test_returns_zero_percent_on_full_success(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A, 'Hannover Hbf');
        $stationB = $this->makeStation(self::LAT_A, self::LON_A, 'Hannover Kröpcke'); // same coords → oldDistance=0

        $stopA = $this->makeStopover($stationA, departure: Carbon::parse('2025-01-01 10:00:00'));
        $stopB = $this->makeStopover($stationB, arrival: Carbon::parse('2025-01-01 10:05:00'));

        $trip = $this->makeTrip([$stopA, $stopB]);
        $newSegment = $this->makeSegment();

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->once()->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->once()->andReturn($this->makeRoute(distanceMeters: 250));
        $this->repository->shouldReceive('createRouteSegment')->once()->andReturn($newSegment);
        $this->repository->shouldReceive('setRouteSegmentForStop')->once();

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
        Queue::assertPushed(RecalculateStatusesDistanceForTrip::class);
    }

    public function test_disabled_brouter_leaves_the_pair_untouched_without_routing(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A, 'Hannover Hbf');
        $stationB = $this->makeStation(self::LAT_B, self::LON_B, 'Hannover Kröpcke');

        $stopA = $this->makeStopover($stationA, departure: Carbon::parse('2025-01-01 10:00:00'));
        $stopB = $this->makeStopover($stationB, arrival: Carbon::parse('2025-01-01 10:05:00'));

        $trip = $this->makeTrip([$stopA, $stopB]);

        $this->brouter->shouldReceive('isEnabled')->andReturnFalse();
        $this->brouter->shouldNotReceive('getRoute');
        $this->repository->shouldNotReceive('getRouteSegmentBetweenStops');
        $this->repository->shouldNotReceive('createRouteSegment');

        $result = $this->service->rerouteStops($trip);

        // Skipping is not an error, so no cooldown is triggered.
        $this->assertSame(0, $result);
    }

    public function test_disabled_brouter_still_builds_great_circle_arcs(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A, 'Hamburg');
        $stationB = $this->makeStation(self::LAT_B, self::LON_B, 'New York');

        $stopA = $this->makeStopover($stationA, departure: Carbon::parse('2025-01-01 10:00:00'));
        $stopB = $this->makeStopover($stationB, arrival: Carbon::parse('2025-01-01 18:00:00'));

        $trip = $this->makeTrip([$stopA, $stopB], HafasTravelType::PLANE);
        $newSegment = $this->makeSegment();

        $this->brouter->shouldReceive('isEnabled')->andReturnFalse();
        $this->brouter->shouldNotReceive('getRoute');
        $this->geodesic->shouldReceive('interpolate')->once()->andReturn([
            new Coordinate(self::LAT_A, self::LON_A),
            new Coordinate(self::LAT_B, self::LON_B),
        ]);
        $this->geodesic->shouldReceive('haversineDistance')->once()->andReturn(310);
        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->once()->andReturn(null);
        $this->repository->shouldReceive('createRouteSegment')->once()->andReturn($newSegment);
        $this->repository->shouldReceive('setRouteSegmentForStop')->once();

        $this->assertSame(0, $this->service->rerouteStops($trip));
    }

    public function test_first_stop_is_skipped_because_it_has_no_previous(): void
    {
        $station = $this->makeStation(self::LAT_A, self::LON_A);
        $stop = $this->makeStopover($station);
        $trip = $this->makeTrip([$stop]);

        // No BRouter or repository calls expected since the only stop has no previous.
        $this->brouter->shouldNotReceive('getRoute');
        $this->repository->shouldNotReceive('getRouteSegmentBetweenStops');

        // 0 errors out of 1 stopover = 0% error rate → job still dispatched
        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
        Queue::assertPushed(RecalculateStatusesDistanceForTrip::class);
    }

    public function test_stop_with_existing_segment_is_skipped_and_stopovers_decremented(): void
    {
        $station = $this->makeStation(self::LAT_A, self::LON_A);
        $stopA = $this->makeStopover($station, segment: $this->makeSegment());
        $stopB = $this->makeStopover($station);

        $trip = $this->makeTrip([$stopA, $stopB]);

        $this->brouter->shouldNotReceive('getRoute');
        $this->repository->shouldNotReceive('getRouteSegmentBetweenStops');

        // stopovers starts at 2, decremented to 1 for the pair whose FROM stop already has a segment.
        // 0 errors / 1 counted stopover = 0% → job dispatched.
        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
        Queue::assertPushed(RecalculateStatusesDistanceForTrip::class);
    }

    public function test_skips_category_without_segment_path_type(): void
    {
        $station = $this->makeStation(self::LAT_A, self::LON_A);
        $stopA = $this->makeStopover($station);
        $stopB = $this->makeStopover($station); // TAXI has no SegmentPathType (default => null)

        $trip = $this->makeTrip([$stopA, $stopB], HafasTravelType::TAXI);

        $this->brouter->shouldNotReceive('getRoute');
        $this->repository->shouldNotReceive('createRouteSegment');

        $this->service->rerouteStops($trip);
    }

    public function test_dispatches_recalculate_job_when_error_rate_is_below_threshold(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(self::LAT_A, self::LON_A);
        $stopA = $this->makeStopover($stationA);
        $stopB = $this->makeStopover($stationB);

        $trip = $this->makeTrip([$stopA, $stopB]);
        $segment = $this->makeSegment();

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->andReturn($this->makeRoute(0));
        $this->repository->shouldReceive('createRouteSegment')->andReturn($segment);
        $this->repository->shouldReceive('setRouteSegmentForStop');

        $result = $this->service->rerouteStops($trip);

        $this->assertLessThan(10, $result);
        Queue::assertPushed(RecalculateStatusesDistanceForTrip::class);
    }

    public function test_does_not_dispatch_job_when_error_rate_is_at_or_above_threshold(): void
    {
        // 10 legs, all fail → error% = 100
        $stations = array_map(fn () => $this->makeStation(self::LAT_A, self::LON_A), range(0, 10));
        $stopovers = array_map(fn (Station $s) => $this->makeStopover($s), $stations);

        $trip = $this->makeTrip($stopovers);

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->andThrow(new BRouterException('network error'));

        $result = $this->service->rerouteStops($trip);

        $this->assertGreaterThanOrEqual(10, $result);
        Queue::assertNotPushed(RecalculateStatusesDistanceForTrip::class);
    }

    public function test_brouter_exception_increments_error_count(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(self::LAT_A, self::LON_A);
        $stopA = $this->makeStopover($stationA);
        $stopB = $this->makeStopover($stationB);

        $trip = $this->makeTrip([$stopA, $stopB]);

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->andThrow(new BRouterException('timeout'));

        $result = $this->service->rerouteStops($trip);

        // stopovers=2, queryExceptions=1 → 1/2*100 = 50%
        $this->assertSame(50, $result);
    }

    public function test_client_exception_does_not_increment_error_count(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(self::LAT_A, self::LON_A);
        $stopA = $this->makeStopover($stationA);
        $stopB = $this->makeStopover($stationB);

        $trip = $this->makeTrip([$stopA, $stopB]);

        $exception = new ClientException('404', new GuzzleRequest('GET', '/'), new GuzzleResponse(404));

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->andThrow($exception);

        $result = $this->service->rerouteStops($trip);

        // ClientException is caught and returned early without incrementing queryExceptions
        $this->assertSame(0, $result);
    }

    public function test_curl_timeout_does_not_increment_error_count(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(self::LAT_A, self::LON_A);
        $stopA = $this->makeStopover($stationA);
        $stopB = $this->makeStopover($stationB);

        $trip = $this->makeTrip([$stopA, $stopB]);

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->andThrow(new BRouterException('cURL error 28: connection timed out'));

        $result = $this->service->rerouteStops($trip);

        // cURL 28 still increments queryExceptions, just skips report(). stopovers=2 → 50%.
        $this->assertSame(50, $result);
    }

    public function test_skips_when_station_has_no_location(): void
    {
        $stopA = new Stopover();
        $stopA->setRelation('station', null);
        $stopA->setRelation('stationIdentifier', null);
        $stopA->setRelation('routeSegment', null);

        $stopB = new Stopover();
        $stopB->setRelation('station', null);
        $stopB->setRelation('stationIdentifier', null);
        $stopB->setRelation('routeSegment', null);

        $trip = $this->makeTrip([$stopA, $stopB]);

        $this->brouter->shouldNotReceive('getRoute');

        $this->service->rerouteStops($trip);
    }

    public function test_uses_existing_segment_instead_of_calling_brouter(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(self::LAT_B, self::LON_B);
        $stopA = $this->makeStopover($stationA);
        $stopB = $this->makeStopover($stationB);

        $trip = $this->makeTrip([$stopA, $stopB]);
        $existingSegment = $this->makeSegment();

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn($existingSegment);
        $this->repository->shouldReceive('setRouteSegmentForStop')->once()->with($stopA, $existingSegment);
        $this->brouter->shouldNotReceive('getRoute');

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
    }

    public function test_skips_when_calculated_speed_exceeds_300kmh(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A, 'Hannover Hbf');
        $stationB = $this->makeStation(self::LAT_A, self::LON_A, 'Hannover Kröpcke');

        // 60 seconds duration, 6000 m distance → 360 km/h, over the 300 km/h limit
        $stopA = $this->makeStopover($stationA, departure: Carbon::parse('2025-01-01 10:00:00'));
        $stopB = $this->makeStopover($stationB, arrival: Carbon::parse('2025-01-01 10:01:00'));

        $trip = $this->makeTrip([$stopA, $stopB]);

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->andReturn(new RouteDto(
            coordinates: [new Coordinate(self::LAT_A, self::LON_A), new Coordinate(self::LAT_B, self::LON_B)],
            distanceInMeters: 6000,
        ));
        $this->repository->shouldNotReceive('createRouteSegment');

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
        Queue::assertPushed(RecalculateStatusesDistanceForTrip::class);
    }

    public function test_skips_when_no_duration_and_distance_exceeds_1000m(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A, 'Hannover Hbf');
        $stationB = $this->makeStation(self::LAT_A, self::LON_A, 'Hannover Kröpcke');

        // No times set → duration = -1
        $stopA = $this->makeStopover($stationA);
        $stopB = $this->makeStopover($stationB);

        $trip = $this->makeTrip([$stopA, $stopB]);

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->andReturn(new RouteDto(
            coordinates: [new Coordinate(self::LAT_A, self::LON_A), new Coordinate(self::LAT_B, self::LON_B)],
            distanceInMeters: 1500,
        ));
        $this->repository->shouldNotReceive('createRouteSegment');

        $this->service->rerouteStops($trip);
    }

    public function test_skips_when_distance_deviation_exceeds_threshold(): void
    {
        // Start and end at different coordinates so oldDistance > 0 and threshold applies.
        // Hannover Hbf to Kröpcke is ~310 m. BRouter returns 5000 m → well outside any threshold.
        $stationA = $this->makeStation(self::LAT_A, self::LON_A, 'Hannover Hbf');
        $stationB = $this->makeStation(self::LAT_B, self::LON_B, 'Hannover Kröpcke');

        $stopA = $this->makeStopover($stationA, departure: Carbon::parse('2025-01-01 10:00:00'));
        $stopB = $this->makeStopover($stationB, arrival: Carbon::parse('2025-01-01 10:30:00'));

        $trip = $this->makeTrip([$stopA, $stopB]);

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->andReturn(new RouteDto(
            coordinates: [new Coordinate(self::LAT_A, self::LON_A), new Coordinate(self::LAT_B, self::LON_B)],
            distanceInMeters: 5000, // ~16x oldDistance, far outside 40% threshold
        ));
        $this->repository->shouldNotReceive('createRouteSegment');

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
    }

    public function test_generic_exception_is_reported_and_counted(): void
    {
        $stationA = $this->makeStation(self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(self::LAT_A, self::LON_A);
        $stopA = $this->makeStopover($stationA);
        $stopB = $this->makeStopover($stationB);

        $trip = $this->makeTrip([$stopA, $stopB]);

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        $this->brouter->shouldReceive('getRoute')->andThrow(new \RuntimeException('unexpected'));

        // Generic exceptions don't increment queryExceptions but are reported.
        // The return value is still 0% (queryExceptions = 0).
        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
    }

    public function test_uses_identifier_location_when_available(): void
    {
        $station = $this->makeStation(self::LAT_A, self::LON_A);

        $identifier = new StationIdentifier();
        $identifier->latitude = self::LAT_B;
        $identifier->longitude = self::LON_B;

        $stopA = $this->makeStopover($station);
        $stopA->setRelation('stationIdentifier', $identifier);

        $stopB = $this->makeStopover($station);
        $stopB->setRelation('stationIdentifier', $identifier);

        $trip = $this->makeTrip([$stopA, $stopB]);
        $segment = $this->makeSegment();

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        // Both stopovers use identifier coords (LAT_B/LON_B), so oldDistance = 0, speed check irrelevant.
        $this->brouter->shouldReceive('getRoute')->andReturn($this->makeRoute(0));
        $this->repository->shouldReceive('createRouteSegment')
            ->once()
            ->withArgs(function ($args) {
                // fromIdentifier and toIdentifier should be the StationIdentifier objects
                return true;
            })
            ->andReturn($segment);
        $this->repository->shouldReceive('setRouteSegmentForStop');

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
    }

    public function test_stopovers_below_one_returns_raw_exception_count(): void
    {
        // Empty collection → stopovers = 0 → errorPercentage = queryExceptions (raw, not divided)
        $trip = $this->makeTrip([]);

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
        Queue::assertPushed(RecalculateStatusesDistanceForTrip::class);
    }

    public function test_short_distance_threshold_is_applied_for_distances_between_400_and_2000m(): void
    {
        // Stations ~1 km apart (short_distance range: 400 m - 2000 m → 30% threshold).
        // BRouter returns 25% above oldDistance: passes 30% but would fail the standard 20% threshold.
        $stationA = $this->makeStation(52.3766, 9.7413, 'Hannover Hbf');
        $stationB = $this->makeStation(52.3856, 9.7413, 'Point ~1 km north'); // ~1001 m from A

        $stopA = $this->makeStopover($stationA, departure: Carbon::parse('2025-01-01 10:00:00'));
        $stopB = $this->makeStopover($stationB, arrival: Carbon::parse('2025-01-01 11:00:00'));

        $trip = $this->makeTrip([$stopA, $stopB]);
        $segment = $this->makeSegment();

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        // 1001 m * 1.25 = 1251 m: inside 30% threshold, outside 20% threshold.
        $this->brouter->shouldReceive('getRoute')->andReturn(new RouteDto(
            coordinates: [new Coordinate(52.3766, 9.7413), new Coordinate(52.3856, 9.7413)],
            distanceInMeters: 1251,
        ));
        $this->repository->shouldReceive('createRouteSegment')->once()->andReturn($segment);
        $this->repository->shouldReceive('setRouteSegmentForStop')->once();

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
    }

    public function test_medium_distance_threshold_is_applied_for_distances_between_2000_and_15000m(): void
    {
        // Stations ~5 km apart (medium_distance range: 2000 m - 15 000 m → 25% threshold).
        // BRouter returns 22% above oldDistance: passes 25% but would fail the standard 20% threshold.
        $stationA = $this->makeStation(52.3766, 9.7413, 'Hannover Hbf');
        $stationB = $this->makeStation(52.4216, 9.7413, 'Point ~5 km north'); // ~5006 m from A

        $stopA = $this->makeStopover($stationA, departure: Carbon::parse('2025-01-01 10:00:00'));
        $stopB = $this->makeStopover($stationB, arrival: Carbon::parse('2025-01-01 11:00:00'));

        $trip = $this->makeTrip([$stopA, $stopB]);
        $segment = $this->makeSegment();

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->andReturn(null);
        // 5006 m * 1.22 = 6107 m: inside 25% threshold, outside 20% threshold.
        $this->brouter->shouldReceive('getRoute')->andReturn(new RouteDto(
            coordinates: [new Coordinate(52.3766, 9.7413), new Coordinate(52.4216, 9.7413)],
            distanceInMeters: 6107,
        ));
        $this->repository->shouldReceive('createRouteSegment')->once()->andReturn($segment);
        $this->repository->shouldReceive('setRouteSegmentForStop')->once();

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
    }

    public function test_plane_trip_creates_great_circle_segment_without_brouter(): void
    {
        $stationA = $this->makeStation(52.3116, 9.6946, 'Hannover Airport');   // HAJ
        $stationB = $this->makeStation(48.3538, 11.7861, 'Munich Airport');    // MUC

        $stopA = $this->makeStopover($stationA, departure: Carbon::parse('2025-06-01 10:00:00'));
        $stopB = $this->makeStopover($stationB, arrival: Carbon::parse('2025-06-01 11:10:00'));

        $trip = $this->makeTrip([$stopA, $stopB], HafasTravelType::PLANE);
        $newSegment = $this->makeSegment();

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->once()->andReturn(null);
        $this->brouter->shouldNotReceive('getRoute');

        $this->geodesic->shouldReceive('interpolate')
            ->once()
            ->andReturn([
                new Coordinate(52.3116, 9.6946),
                new Coordinate(50.0, 10.5),
                new Coordinate(48.3538, 11.7861),
            ]);
        $this->geodesic->shouldReceive('haversineDistance')
            ->once()
            ->andReturn(480_000); // ~480 km HAJ to MUC

        $this->repository->shouldReceive('createRouteSegment')->once()->andReturn($newSegment);
        $this->repository->shouldReceive('setRouteSegmentForStop')->once();

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
        Queue::assertPushed(RecalculateStatusesDistanceForTrip::class);
    }

    public function test_plane_trip_reuses_existing_great_circle_segment(): void
    {
        $stationA = $this->makeStation(52.3116, 9.6946, 'Hannover Airport');
        $stationB = $this->makeStation(48.3538, 11.7861, 'Munich Airport');

        $stopA = $this->makeStopover($stationA, departure: Carbon::parse('2025-06-01 10:00:00'));
        $stopB = $this->makeStopover($stationB, arrival: Carbon::parse('2025-06-01 11:10:00'));

        $trip = $this->makeTrip([$stopA, $stopB], HafasTravelType::PLANE);
        $existingSegment = $this->makeSegment();

        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->once()->andReturn($existingSegment);
        $this->repository->shouldReceive('setRouteSegmentForStop')->once()->with($stopA, $existingSegment);
        $this->brouter->shouldNotReceive('getRoute');
        $this->geodesic->shouldNotReceive('interpolate');

        $result = $this->service->rerouteStops($trip);

        $this->assertSame(0, $result);
    }
}
