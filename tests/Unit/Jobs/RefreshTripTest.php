<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\DataProviders\Hydrators\MotisHydrator;
use App\Jobs\RefreshTrip;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
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

    public function test_uses_first_leg_when_it_matches_trip_id(): void
    {
        $trip = $this->mockTrip('trip-A', 'ICE 1');

        Http::fake([
            'https://api.transitous.org/api/v5/trip*' => Http::response([
                'legs' => [$this->makeLeg('trip-A', true)],
            ], 200),
        ]);

        $hydrator = Mockery::mock(MotisHydrator::class);
        $hydrator->shouldReceive('parseLegToUpdateStopovers')
            ->once()
            ->withArgs(fn (array $leg) => $leg['tripId'] === 'trip-A')
            ->andReturn(new Collection());

        new RefreshTrip($trip)->handle($hydrator);
    }

    public function test_finds_matching_leg_when_not_the_first_leg(): void
    {
        $trip = $this->mockTrip('trip-B', 'ECE 7');

        Http::fake([
            'https://api.transitous.org/api/v5/trip*' => Http::response([
                'legs' => [
                    $this->makeLeg('trip-A', true),  // first leg belongs to a different trip
                    $this->makeLeg('trip-B', true),  // second leg is the one we want
                ],
            ], 200),
        ]);

        $hydrator = Mockery::mock(MotisHydrator::class);
        $hydrator->shouldReceive('parseLegToUpdateStopovers')
            ->once()
            ->withArgs(fn (array $leg) => $leg['tripId'] === 'trip-B')
            ->andReturn(new Collection());

        new RefreshTrip($trip)->handle($hydrator);
    }

    public function test_skips_when_matching_leg_has_no_realtime(): void
    {
        $trip = $this->mockTrip('trip-B', 'ECE 7');

        Http::fake([
            'https://api.transitous.org/api/v5/trip*' => Http::response([
                'legs' => [
                    $this->makeLeg('trip-A', true),
                    $this->makeLeg('trip-B', false),
                ],
            ], 200),
        ]);

        $hydrator = Mockery::mock(MotisHydrator::class);
        $hydrator->shouldNotReceive('parseLegToUpdateStopovers');

        new RefreshTrip($trip)->handle($hydrator);
    }

    public function test_skips_when_no_matching_leg_found(): void
    {
        $trip = $this->mockTrip('trip-unknown', 'ECE 7');

        Http::fake([
            'https://api.transitous.org/api/v5/trip*' => Http::response([
                'legs' => [
                    $this->makeLeg('trip-A', true),
                    $this->makeLeg('trip-B', true),
                ],
            ], 200),
        ]);

        $hydrator = Mockery::mock(MotisHydrator::class);
        $hydrator->shouldNotReceive('parseLegToUpdateStopovers');

        new RefreshTrip($trip)->handle($hydrator);
    }
}
