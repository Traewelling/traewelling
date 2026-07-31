<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Trip;

use App\Dto\Coordinate;
use App\Enum\HafasTravelType;
use App\Enum\SegmentPathType;
use App\Enum\TripSource;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use App\Repositories\TripRepository;
use App\Services\GeodesicService;
use App\Services\Trip\ProviderPolylineService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\Unit\UnitTestCase;
use Traewelling\GooglePolyline\PolylineTranscoder;

class ProviderPolylineServiceTest extends UnitTestCase
{
    private const string TRIP_ID = 'test-trip-123';

    /** Hannover Hbf */
    private const float LAT_A = 52.3766;

    private const float LON_A = 9.7413;

    /** Hannover Kröpcke, ~310 m south-west */
    private const float LAT_B = 52.3744;

    private const float LON_B = 9.7385;

    /** Hannover Aegidientorplatz, ~350 m further south-east */
    private const float LAT_C = 52.3719;

    private const float LON_C = 9.7423;

    private TripRepository $repository;

    private ProviderPolylineService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TripRepository::class);
        $this->service = new ProviderPolylineService($this->repository, new GeodesicService());
    }

    private function makeStation(int $id, float $lat, float $lon, string $name = 'Station'): Station
    {
        $station = new Station();
        $station->id = $id;
        $station->latitude = $lat;
        $station->longitude = $lon;
        $station->name = $name;

        return $station;
    }

    private function makeStopover(
        Station $station,
        ?string $segmentId = null,
        ?Carbon $departure = null,
        ?Carbon $arrival = null,
    ): Stopover {
        $stopover = new Stopover();

        // Bypass the UTCDateTime cast by setting raw attributes directly
        $stopover->setRawAttributes(array_filter([
            'train_station_id' => $station->id,
            'route_segment_id' => $segmentId,
            'departure_planned' => $departure?->toDateTimeString(),
            'arrival_planned' => $arrival?->toDateTimeString(),
        ]));
        $stopover->setRelation('station', $station);
        $stopover->setRelation('stationIdentifier', null);

        return $stopover;
    }

    /**
     * @param  Stopover[]  $stopovers
     */
    private function makeTrip(
        array $stopovers,
        TripSource $source = TripSource::TRANSITOUS,
        HafasTravelType $category = HafasTravelType::NATIONAL_EXPRESS,
    ): Trip {
        $builder = Mockery::mock(HasMany::class);
        $builder->shouldReceive('with')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect($stopovers));

        $trip = Mockery::mock(Trip::class)->makePartial();
        $trip->shouldReceive('stopovers')->andReturn($builder);
        $trip->id = 42;
        $trip->trip_id = self::TRIP_ID;
        $trip->linename = 'ICE 123';
        $trip->category = $category;
        $trip->source = $source;

        return $trip;
    }

    /**
     * Builds a shape that runs A -> B -> C with intermediate points, so slicing it at the
     * stopovers has to yield real sub-lines rather than the bare station coordinates.
     *
     * @return Coordinate[]
     */
    private function makeShape(): array
    {
        $waypoints = [
            [self::LAT_A, self::LON_A],
            [self::LAT_B, self::LON_B],
            [self::LAT_C, self::LON_C],
        ];

        $shape = [];
        for ($leg = 1; $leg < count($waypoints); $leg++) {
            [$fromLat, $fromLon] = $waypoints[$leg - 1];
            [$toLat, $toLon] = $waypoints[$leg];
            // 10 steps per leg; the last point of a leg is the first of the next one
            for ($step = 0; $step < 10; $step++) {
                $t = $step / 10;
                $shape[] = new Coordinate($fromLat + ($toLat - $fromLat) * $t, $fromLon + ($toLon - $fromLon) * $t);
            }
        }
        $shape[] = new Coordinate(self::LAT_C, self::LON_C);

        return $shape;
    }

    /**
     * @param  Coordinate[]  $shape
     */
    private function fakeProviderResponse(array $shape, int $precision = 6, string $tripId = self::TRIP_ID): void
    {
        $points = new PolylineTranscoder()->encodePolyline(
            array_map(static fn (Coordinate $c): array => [$c->longitude, $c->latitude], $shape),
            $precision,
        );

        Http::fake([
            '*' => Http::response([
                'legs' => [
                    [
                        'tripId' => $tripId,
                        'legGeometry' => ['points' => $points, 'precision' => $precision, 'length' => count($shape)],
                    ],
                ],
            ]),
        ]);
    }

    public function test_creates_a_route_segment_per_stopover_pair_from_provider_geometry(): void
    {
        $stationA = $this->makeStation(1, self::LAT_A, self::LON_A, 'Hannover Hbf');
        $stationB = $this->makeStation(2, self::LAT_B, self::LON_B, 'Kröpcke');
        $stationC = $this->makeStation(3, self::LAT_C, self::LON_C, 'Aegidientorplatz');

        $trip = $this->makeTrip([
            $this->makeStopover($stationA, departure: Carbon::parse('2026-01-01 10:00:00')),
            $this->makeStopover($stationB, departure: Carbon::parse('2026-01-01 10:05:00'), arrival: Carbon::parse('2026-01-01 10:04:00')),
            $this->makeStopover($stationC, arrival: Carbon::parse('2026-01-01 10:09:00')),
        ]);
        $this->fakeProviderResponse($this->makeShape());

        $captured = [];
        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->twice()->andReturn(null);
        $this->repository->shouldReceive('createRouteSegment')->twice()
            ->andReturnUsing(function (...$args) use (&$captured) {
                $captured[] = $args;

                return new RouteSegment();
            });
        $this->repository->shouldReceive('setRouteSegmentForStop')->twice();

        $result = $this->service->importForTrip($trip);

        $this->assertSame(2, $result->created);
        $this->assertSame(0, $result->reused);
        $this->assertNull($result->abortReason);
        $this->assertCount(2, $captured);

        // Mockery hands named arguments over positionally, in createRouteSegment's parameter order
        [$fromStation, $toStation, $encodedPolyline, $precision, $duration, $pathType, $distance] = $captured[0];
        $this->assertSame(6, $precision);
        $this->assertSame(SegmentPathType::RAIL, $pathType);
        // departure at A (10:00) until arrival at B (10:04)
        $this->assertSame(240, $duration);
        $this->assertSame($stationA, $fromStation);
        $this->assertSame($stationB, $toStation);

        // The A->B leg is ~310 m long; the sliced polyline must reproduce that, not a single point
        $this->assertGreaterThan(250, $distance);
        $this->assertLessThan(400, $distance);

        $decoded = new PolylineTranscoder()->decodePolyline($encodedPolyline, 6);
        $this->assertGreaterThan(2, count($decoded));
        $this->assertEqualsWithDelta(self::LAT_A, $decoded[0]->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(self::LAT_B, end($decoded)->getLatitude(), 0.0001);
    }

    public function test_reuses_an_existing_segment_instead_of_creating_a_new_one(): void
    {
        $stationA = $this->makeStation(1, self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(2, self::LAT_B, self::LON_B);

        $trip = $this->makeTrip([
            $this->makeStopover($stationA, departure: Carbon::parse('2026-01-01 10:00:00')),
            $this->makeStopover($stationB, arrival: Carbon::parse('2026-01-01 10:05:00')),
        ]);
        $this->fakeProviderResponse($this->makeShape());

        $existing = new RouteSegment();
        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->once()->andReturn($existing);
        $this->repository->shouldNotReceive('createRouteSegment');
        $this->repository->shouldReceive('setRouteSegmentForStop')->once()->with(Mockery::any(), $existing);

        $result = $this->service->importForTrip($trip);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->reused);
    }

    public function test_stopovers_that_already_have_a_segment_are_left_untouched(): void
    {
        $stationA = $this->makeStation(1, self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(2, self::LAT_B, self::LON_B);
        $stationC = $this->makeStation(3, self::LAT_C, self::LON_C);

        $trip = $this->makeTrip([
            $this->makeStopover($stationA, segmentId: 'existing-uuid', departure: Carbon::parse('2026-01-01 10:00:00')),
            $this->makeStopover($stationB, departure: Carbon::parse('2026-01-01 10:05:00')),
            $this->makeStopover($stationC, arrival: Carbon::parse('2026-01-01 10:09:00')),
        ]);
        $this->fakeProviderResponse($this->makeShape());

        // Only the B->C pair is still open
        $this->repository->shouldReceive('getRouteSegmentBetweenStops')->once()->andReturn(null);
        $this->repository->shouldReceive('createRouteSegment')->once()->andReturn(new RouteSegment());
        $this->repository->shouldReceive('setRouteSegmentForStop')->once();

        $result = $this->service->importForTrip($trip);

        $this->assertSame(1, $result->created);
    }

    public function test_does_not_query_the_provider_when_every_pair_already_has_a_segment(): void
    {
        $stationA = $this->makeStation(1, self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(2, self::LAT_B, self::LON_B);

        $trip = $this->makeTrip([
            $this->makeStopover($stationA, segmentId: 'existing-uuid'),
            $this->makeStopover($stationB),
        ]);
        Http::fake();

        $this->repository->shouldNotReceive('createRouteSegment');

        $result = $this->service->importForTrip($trip);

        $this->assertSame(0, $result->created);
        $this->assertNull($result->abortReason);
        Http::assertNothingSent();
    }

    public function test_aborts_when_the_stopovers_do_not_lie_on_the_geometry(): void
    {
        // Shape runs through Hannover, but the trip claims to stop in Berlin
        $stationA = $this->makeStation(1, 52.5251, 13.3694, 'Berlin Hbf');
        $stationB = $this->makeStation(2, 52.5060, 13.3325, 'Zoologischer Garten');

        $trip = $this->makeTrip([
            $this->makeStopover($stationA, departure: Carbon::parse('2026-01-01 10:00:00')),
            $this->makeStopover($stationB, arrival: Carbon::parse('2026-01-01 10:05:00')),
        ]);
        $this->fakeProviderResponse($this->makeShape());

        $this->repository->shouldNotReceive('createRouteSegment');

        $result = $this->service->importForTrip($trip);

        $this->assertSame('stopovers do not lie on the provider geometry', $result->abortReason);
    }

    public function test_aborts_when_the_provider_returns_no_geometry_for_this_trip(): void
    {
        $stationA = $this->makeStation(1, self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(2, self::LAT_B, self::LON_B);

        $trip = $this->makeTrip([
            $this->makeStopover($stationA, departure: Carbon::parse('2026-01-01 10:00:00')),
            $this->makeStopover($stationB, arrival: Carbon::parse('2026-01-01 10:05:00')),
        ]);
        // The response only carries a neighbouring leg, never the requested trip
        $this->fakeProviderResponse($this->makeShape(), tripId: 'some-other-trip');

        $this->repository->shouldNotReceive('createRouteSegment');

        $result = $this->service->importForTrip($trip);

        $this->assertSame('provider returned no usable geometry', $result->abortReason);
    }

    public function test_aborts_without_throwing_when_the_provider_no_longer_knows_the_trip(): void
    {
        $stationA = $this->makeStation(1, self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(2, self::LAT_B, self::LON_B);

        $trip = $this->makeTrip([
            $this->makeStopover($stationA, departure: Carbon::parse('2026-01-01 10:00:00')),
            $this->makeStopover($stationB, arrival: Carbon::parse('2026-01-01 10:05:00')),
        ]);
        // Transitous drops trips a few weeks after they ran
        Http::fake(['*' => Http::response(['error' => 'trip not found: tripId=…'], 404)]);

        $this->repository->shouldNotReceive('createRouteSegment');

        $result = $this->service->importForTrip($trip);

        $this->assertSame('provider journey lookup failed', $result->abortReason);
    }

    public function test_aborts_for_trips_that_do_not_come_from_the_provider(): void
    {
        $stationA = $this->makeStation(1, self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(2, self::LAT_B, self::LON_B);

        $trip = $this->makeTrip([
            $this->makeStopover($stationA),
            $this->makeStopover($stationB),
        ], source: TripSource::USER);
        Http::fake();

        $this->repository->shouldNotReceive('createRouteSegment');

        $result = $this->service->importForTrip($trip);

        $this->assertSame('trip is not from a geometry-carrying provider', $result->abortReason);
        Http::assertNothingSent();
    }

    public function test_aborts_for_transport_modes_without_a_segment_path_type(): void
    {
        $stationA = $this->makeStation(1, self::LAT_A, self::LON_A);
        $stationB = $this->makeStation(2, self::LAT_B, self::LON_B);

        $trip = $this->makeTrip([
            $this->makeStopover($stationA),
            $this->makeStopover($stationB),
        ], category: HafasTravelType::TAXI);
        Http::fake();

        $this->repository->shouldNotReceive('createRouteSegment');

        $result = $this->service->importForTrip($trip);

        $this->assertSame('transport mode has no segment path type', $result->abortReason);
        Http::assertNothingSent();
    }
}
