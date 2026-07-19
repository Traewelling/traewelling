<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Cleanup;

use App\Dto\DuplicateStopoverPair;
use App\Jobs\RefreshPolyline;
use App\Models\Checkin;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use App\Services\Cleanup\DuplicateStopoverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DuplicateStopoverServiceTest extends TestCase
{
    use RefreshDatabase;

    private DuplicateStopoverService $service;

    private const string TIME = '2025-11-02T14:15:00Z';

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DuplicateStopoverService::class);
    }

    private function station(float $latitude, float $longitude): Station
    {
        return Station::factory()->create(['latitude' => $latitude, 'longitude' => $longitude]);
    }

    private function stopover(string $tripId, Station $station, string $createdAt, string $time = self::TIME): Stopover
    {
        $stopover = Stopover::factory()->create([
            'trip_id' => $tripId,
            'train_station_id' => $station->id,
            'arrival_planned' => $time,
            'departure_planned' => $time,
        ]);
        // created_at drives keeper/duplicate ordering; set it explicitly.
        DB::table('train_stopovers')->where('id', $stopover->id)->update(['created_at' => $createdAt]);

        return $stopover->fresh();
    }

    public function test_it_detects_a_later_created_same_stop_duplicate(): void
    {
        // Thayngen and Thayngen Bahnhof: same place, ~35 m apart.
        $trip = Trip::factory()->create();
        $keeperStation = $this->station(47.745521, 8.704525);
        $duplicateStation = $this->station(47.745782, 8.704273);

        $keeper = $this->stopover($trip->trip_id, $keeperStation, '2025-11-02 14:01:40');
        $duplicate = $this->stopover($trip->trip_id, $duplicateStation, '2025-11-02 14:20:09');

        /** @var DuplicateStopoverPair[] $pairs */
        $pairs = iterator_to_array($this->service->findDuplicates(200));

        $this->assertCount(1, $pairs);
        $this->assertSame($duplicate->id, $pairs[0]->duplicate->id);
        $this->assertSame($keeper->id, $pairs[0]->keeper->id);
    }

    public function test_it_ignores_stations_that_are_far_apart(): void
    {
        $trip = Trip::factory()->create();
        $stationA = $this->station(47.745521, 8.704525);
        $stationFar = $this->station(48.0, 9.0); // ~30 km away

        $this->stopover($trip->trip_id, $stationA, '2025-11-02 14:01:40');
        $this->stopover($trip->trip_id, $stationFar, '2025-11-02 14:20:09');

        $this->assertCount(0, iterator_to_array($this->service->findDuplicates(200)));
    }

    public function test_it_ignores_stops_created_together(): void
    {
        // Same place and time, but created in one fetch (coarse timetable), not a duplicate.
        $trip = Trip::factory()->create();
        $stationA = $this->station(47.745521, 8.704525);
        $stationB = $this->station(47.745782, 8.704273);

        $this->stopover($trip->trip_id, $stationA, '2025-11-02 14:01:40');
        $this->stopover($trip->trip_id, $stationB, '2025-11-02 14:01:40');

        $this->assertCount(0, iterator_to_array($this->service->findDuplicates(200)));
    }

    public function test_fix_repoints_checkins_and_deletes_the_duplicate(): void
    {
        $trip = Trip::factory()->create();
        $keeperStation = $this->station(47.745521, 8.704525);
        $duplicateStation = $this->station(47.745782, 8.704273);
        $keeper = $this->stopover($trip->trip_id, $keeperStation, '2025-11-02 14:01:40');
        $duplicate = $this->stopover($trip->trip_id, $duplicateStation, '2025-11-02 14:20:09');

        $checkin = Checkin::factory()->create([
            'origin_stopover_id' => $duplicate->id,
            'destination_stopover_id' => $keeper->id,
        ]);

        $repointed = $this->service->fix(new DuplicateStopoverPair($duplicate, $keeper));

        $this->assertSame(1, $repointed);
        $this->assertDatabaseMissing('train_stopovers', ['id' => $duplicate->id]);
        $this->assertSame($keeper->id, $checkin->fresh()->origin_stopover_id);
    }

    public function test_fix_skips_when_repointing_would_conflict_with_existing_checkin(): void
    {
        $trip = Trip::factory()->create();
        $user = User::factory()->create();
        $keeperStation = $this->station(47.745521, 8.704525);
        $duplicateStation = $this->station(47.745782, 8.704273);
        $keeper = $this->stopover($trip->trip_id, $keeperStation, '2025-11-02 14:01:40');
        $duplicate = $this->stopover($trip->trip_id, $duplicateStation, '2025-11-02 14:20:09');

        // Same user already has a check-in on the keeper for this trip.
        Checkin::factory()->create([
            'user_id' => $user->id,
            'trip_id' => $trip->trip_id,
            'origin_stopover_id' => $keeper->id,
            'destination_stopover_id' => $keeper->id,
        ]);
        // ...and one on the duplicate. Repointing it to the keeper would violate
        // the (user_id, trip_id, origin_stopover_id) unique constraint.
        Checkin::factory()->create([
            'user_id' => $user->id,
            'trip_id' => $trip->trip_id,
            'origin_stopover_id' => $duplicate->id,
            'destination_stopover_id' => $duplicate->id,
        ]);

        $result = $this->service->fix(new DuplicateStopoverPair($duplicate, $keeper));

        $this->assertNull($result);
        $this->assertDatabaseHas('train_stopovers', ['id' => $duplicate->id]);
    }

    public function test_refresh_affected_trips_clears_segments_and_dispatches_refresh(): void
    {
        Bus::fake();
        $trip = Trip::factory()->create();
        $from = $this->station(47.0, 8.0);
        $to = $this->station(47.1, 8.1);
        $segment = RouteSegment::factory()->create([
            'from_station_id' => $from->id,
            'to_station_id' => $to->id,
        ]);
        $stopover = $this->stopover($trip->trip_id, $from, '2025-11-02 14:01:40');
        DB::table('train_stopovers')->where('id', $stopover->id)->update(['route_segment_id' => $segment->id]);

        $this->service->refreshAffectedTrips([$trip->trip_id]);

        $this->assertNull($stopover->fresh()->route_segment_id);
        Bus::assertDispatched(RefreshPolyline::class);
    }
}
