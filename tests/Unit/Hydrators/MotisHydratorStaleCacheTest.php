<?php

declare(strict_types=1);

namespace Tests\Unit\Hydrators;

use App\DataProviders\Hydrators\MotisHydrator;
use App\Enum\DataProvider;
use App\Enum\StationIdentifierType;
use App\Models\Station;
use App\Models\StationIdentifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\UnitTestCase;

/**
 * Reproduces issue #4951: feeds with non-static stop IDs (e.g. CZ) reassign the same stopId
 * to a different physical stop on every export. The cached identifier -> station mapping then
 * points to a stale location
 *
 * The hydrator must reject a cache hit whose station is far away from the fresh raw coordinates,
 * re-resolve the stop and repair the identifier mapping.
 */
class MotisHydratorStaleCacheTest extends UnitTestCase
{
    use RefreshDatabase;

    private const string REUSED_IDENTIFIER = 'cz-Bean-Shuttle_2';

    /**
     * @param  array<int, array<string, mixed>>  $intermediateStops
     * @return array<string, mixed>
     */
    private function buildLeg(array $from, array $to, array $intermediateStops = []): array
    {
        return [
            'intermediateStops' => $intermediateStops,
            'from' => $from,
            'to' => $to,
            'realTime' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStop(string $stopId, string $name, float $lat, float $lon, string $time): array
    {
        return [
            'stopId' => $stopId,
            'name' => $name,
            'lat' => $lat,
            'lon' => $lon,
            'scheduledArrival' => $time,
            'scheduledDeparture' => $time,
        ];
    }

    private function createCachedStation(string $name, float $lat, float $lon, string $identifier): Station
    {
        $station = Station::factory()->create(['name' => $name, 'latitude' => $lat, 'longitude' => $lon]);
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'identifier' => $identifier,
            'type' => StationIdentifierType::MOTIS,
            'origin' => DataProvider::TRANSITOUS->value,
            'name' => $name,
            'latitude' => $lat,
            'longitude' => $lon,
        ]);

        return $station;
    }

    public function test_stale_cached_identifier_is_reresolved_to_fresh_coordinates(): void
    {
        // stable endpoint stops whose cached coordinates match the fresh data
        $this->createCachedStation('Ústí nad Labem', 50.6607, 14.0399, 'cz-static_from');
        $this->createCachedStation('Chomutov', 50.6009, 13.6321, 'cz-static_to');

        // the reused identifier currently points to a Prague station (stale export)
        $pragueStation = $this->createCachedStation('Praha', 50.0755, 14.4378, self::REUSED_IDENTIFIER);

        // in the current export the same stopId is a stop in Děčín, ~78 km away
        $leg = $this->buildLeg(
            from: $this->buildStop('cz-static_from', 'Ústí nad Labem', 50.6607, 14.0399, '2025-05-01T10:00:00Z'),
            to: $this->buildStop('cz-static_to', 'Chomutov', 50.6009, 13.6321, '2025-05-01T11:00:00Z'),
            intermediateStops: [
                $this->buildStop(self::REUSED_IDENTIFIER, 'Děčín, hl.nádr.', 50.7749, 14.1961, '2025-05-01T10:30:00Z'),
            ],
        );

        $stopovers = new MotisHydrator()->parseLegToNewStopovers($leg, DataProvider::TRANSITOUS);

        $reusedIdentifier = StationIdentifier::where('identifier', self::REUSED_IDENTIFIER)
            ->where('type', StationIdentifierType::MOTIS)
            ->firstOrFail();
        $resolvedStation = Station::findOrFail($reusedIdentifier->station_id);

        $this->assertNotEquals(
            $pragueStation->id,
            $resolvedStation->id,
            'The stale Prague mapping must not be reused for the Děčín stop'
        );
        $this->assertEqualsWithDelta(50.7749, (float) $resolvedStation->latitude, 0.01, 'The stop must resolve to the fresh Děčín coordinates');
        $this->assertEqualsWithDelta(14.1961, (float) $resolvedStation->longitude, 0.01);

        $intermediateStopover = $stopovers->firstOrFail();
        $this->assertEquals(
            $resolvedStation->id,
            $intermediateStopover->train_station_id,
            'The stopover must point to the freshly resolved station, not the stale one'
        );

        $this->assertNotNull(Station::find($pragueStation->id), 'The stale Prague station itself must stay untouched');
    }

    public function test_cached_identifier_within_threshold_is_reused(): void
    {
        // cached station whose coordinates only differ by a few meters from the fresh data
        $station = $this->createCachedStation('Děčín, hl.nádr.', 50.7749, 14.1961, self::REUSED_IDENTIFIER);
        $stationCountBefore = Station::count();

        $leg = $this->buildLeg(
            from: $this->buildStop('cz-static_from', 'Ústí nad Labem', 50.6607, 14.0399, '2025-05-01T10:00:00Z'),
            to: $this->buildStop('cz-static_to', 'Chomutov', 50.6009, 13.6321, '2025-05-01T11:00:00Z'),
            intermediateStops: [
                $this->buildStop(self::REUSED_IDENTIFIER, 'Děčín, hl.nádr.', 50.77495, 14.19615, '2025-05-01T10:30:00Z'),
            ],
        );

        $stopovers = new MotisHydrator()->parseLegToNewStopovers($leg, DataProvider::TRANSITOUS);

        $this->assertEquals(
            $station->id,
            $stopovers->firstOrFail()->train_station_id,
            'A cache hit within the distance threshold must be reused'
        );
        // the reused-identifier stop must not have created a new station
        $this->assertEquals($stationCountBefore, Station::where('name', 'Děčín, hl.nádr.')->count());
    }
}
