<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\DataProviders\Hydrators\MotisHydrator;
use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use App\Jobs\RefreshPolyline;
use App\Jobs\RefreshTrip;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\Unit\UnitTestCase;

class RefreshTripTest extends UnitTestCase
{
    use RefreshDatabase;

    private function makeLeg(string $tripId, bool $realTime, array $stops = []): array
    {
        return [
            'tripId' => $tripId,
            'realTime' => $realTime,
            'intermediateStops' => $stops,
            'from' => ['stopId' => 'stop:1'],
            'to' => ['stopId' => 'stop:2'],
        ];
    }

    private function mockTrip(string $tripId, string $linename): Trip
    {
        $trip = Mockery::mock(Trip::class);
        $trip->shouldReceive('getAttribute')->with('trip_id')->andReturn($tripId);
        $trip->shouldReceive('getAttribute')->with('linename')->andReturn($linename);
        $trip->shouldReceive('update')->once()->andReturnTrue();

        return $trip;
    }

    public function test_skips_when_leg_has_no_realtime(): void
    {
        $trip = $this->mockTrip('trip-A', 'ECE 7');

        Http::fake([
            'https://api.transitous.org/api/*/trip*' => Http::response([
                'legs' => [$this->makeLeg('trip-A', false)],
            ], 200),
        ]);

        $hydrator = Mockery::mock(MotisHydrator::class);
        $hydrator->shouldNotReceive('parseLegToUpdateStopovers');

        new RefreshTrip($trip)->handle($hydrator);
    }

    public function test_skips_when_journey_has_no_legs(): void
    {
        $trip = $this->mockTrip('trip-A', 'ECE 7');

        Http::fake([
            'https://api.transitous.org/api/*/trip*' => Http::response(['legs' => []], 200),
        ]);

        $hydrator = Mockery::mock(MotisHydrator::class);
        $hydrator->shouldNotReceive('parseLegToUpdateStopovers');

        new RefreshTrip($trip)->handle($hydrator);
    }

    private function createTripWithAssignedSegment(HafasTravelType $category = HafasTravelType::REGIONAL): array
    {
        $origin = Station::factory()->create();
        $destination = Station::factory()->create();

        $trip = Trip::create([
            'trip_id' => 'trip-reset',
            'category' => $category,
            'number' => 'RB 1',
            'linename' => 'RB 1',
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'departure' => '2026-07-17T10:00:00Z',
            'arrival' => '2026-07-17T11:00:00Z',
            'source' => TripSource::TRANSITOUS,
        ]);

        $segment = RouteSegment::factory()->create();
        $stopover = Stopover::factory()->create([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $origin->id,
            'route_segment_id' => $segment->id,
        ]);

        Http::fake([
            'https://api.transitous.org/api/*/trip*' => Http::response([
                'legs' => [$this->makeLeg('trip-reset', true)],
            ], 200),
        ]);

        return [$trip, $stopover];
    }

    private function mockHydratorReturningNewStopover(): MotisHydrator
    {
        $newStopover = new Stopover();
        $newStopover->wasRecentlyCreated = true;

        $hydrator = Mockery::mock(MotisHydrator::class);
        $hydrator->shouldReceive('parseLegToUpdateStopovers')
            ->once()
            ->andReturn(new Collection([$newStopover]));

        return $hydrator;
    }

    public function test_resets_route_segments_and_dispatches_polyline_refresh_when_new_stopovers_were_created(): void
    {
        Bus::fake([RefreshPolyline::class]);
        [$trip, $stopover] = $this->createTripWithAssignedSegment();

        new RefreshTrip($trip)->handle($this->mockHydratorReturningNewStopover());

        $this->assertNull($stopover->fresh()->route_segment_id);
        Bus::assertDispatched(RefreshPolyline::class);
    }

    public function test_keeps_route_segments_when_no_new_stopovers_were_created(): void
    {
        Bus::fake([RefreshPolyline::class]);
        [$trip, $stopover] = $this->createTripWithAssignedSegment();

        $hydrator = Mockery::mock(MotisHydrator::class);
        $hydrator->shouldReceive('parseLegToUpdateStopovers')
            ->once()
            ->andReturn(new Collection([$stopover->fresh()]));

        new RefreshTrip($trip)->handle($hydrator);

        $this->assertNotNull($stopover->fresh()->route_segment_id);
        Bus::assertNotDispatched(RefreshPolyline::class);
    }

    public function test_keeps_route_segments_for_non_rail_trips(): void
    {
        Bus::fake([RefreshPolyline::class]);
        [$trip, $stopover] = $this->createTripWithAssignedSegment(HafasTravelType::BUS);

        new RefreshTrip($trip)->handle($this->mockHydratorReturningNewStopover());

        $this->assertNotNull($stopover->fresh()->route_segment_id);
        Bus::assertNotDispatched(RefreshPolyline::class);
    }
}
