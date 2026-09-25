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

    /**
     * Issue #4910: purely numeric DELFI ids are reused by other stops in later exports. For those ids
     * updateOrCreateByIfopt used to find the stale mapping again after the cache hit was rejected.
     */
    public function test_stale_numeric_delfi_identifier_is_reresolved_on_every_lookup_path(): void
    {
        $staleStation = $this->createCachedStation('Magdeburgerforth, Kleinbahnhof', 52.2325, 12.1959, 'de-DELFI_000000000001_G');
        $this->createCachedStation('Hettstedt Kupferkammerhütte', 51.63311, 11.510762, 'de-DELFI_000000000008');
        $benndorf = $this->buildStop('de-DELFI_000000000001_G', 'Benndorf (Mansfelder Land), Bahnhof', 51.575455, 11.492014, '2026-09-25T10:00:00Z');
        $hettstedt = $this->buildStop('de-DELFI_000000000008', 'Hettstedt Kupferkammerhütte', 51.63311, 11.510762, '2026-09-25T11:00:00Z');
        $hydrator = new MotisHydrator();

        $tripData = $hydrator->getTripData(
            array_merge($this->buildLeg($benndorf, $hettstedt), ['mode' => 'RAIL', 'source' => 'de-DELFI.gtfs.zip']),
            'MBB',
            DataProvider::TRANSITOUS
        );
        $stopovers = $hydrator->parseLegToNewStopovers($this->buildLeg($benndorf, $hettstedt), DataProvider::TRANSITOUS);
        $departures = $hydrator->mapDepartures(
            [[
                'tripId' => 'mbb-1',
                'place' => $benndorf,
                'mode' => 'RAIL',
                'source' => 'de-DELFI.gtfs.zip',
                'headsign' => 'Hettstedt Kupferkammerhütte',
                'displayName' => 'MBB',
            ]],
            Station::where('name', 'Hettstedt Kupferkammerhütte')->firstOrFail(),
            DataProvider::TRANSITOUS,
            'de-DELFI_000000000008'
        );

        $retiredIdentifier = StationIdentifier::where('identifier', 'like', 'de-DELFI_000000000001_G' . StationIdentifier::RETIRED_MARKER . '%')->firstOrFail();
        $this->assertSame($staleStation->id, $retiredIdentifier->station_id, 'Stopovers and route segments keep pointing to the retired row, so it must keep its station');
        $this->assertSame(-9_000, $retiredIdentifier->relevance);

        $benndorfStation = StationIdentifier::where('identifier', 'de-DELFI_000000000001_G')->firstOrFail()->station;
        $this->assertNotEquals($staleStation->id, $benndorfStation->id);
        $this->assertSame('Benndorf (Mansfelder Land), Bahnhof', $benndorfStation->name);
        $this->assertSame($benndorfStation->id, $tripData['origin_id']);
        $this->assertSame($benndorfStation->id, $stopovers->firstOrFail()->train_station_id);
        $this->assertSame($benndorfStation->id, $departures->departures->firstOrFail()->station->id);
        $this->assertSame('Magdeburgerforth, Kleinbahnhof', $staleStation->fresh()->name);
    }
}
