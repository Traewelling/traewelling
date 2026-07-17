<?php

declare(strict_types=1);

namespace Tests\Unit\Hydrators;

use App\DataProviders\Hydrators\MotisHydrator;
use App\Enum\DataProvider;
use App\Enum\HafasTravelType;
use App\Enum\StationIdentifierType;
use App\Enum\TripSource;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\UnitTestCase;

/**
 * Reproduces the duplicate-stopover bug that occurs when a StationIdentifier
 * is moved to another station (e.g. while merging duplicate stations) while
 * a trip using that station is still active and gets real-time refreshes.
 */
class MotisHydratorStationMergeTest extends UnitTestCase
{
    use RefreshDatabase;

    private const string TRIP_ID = '20250501:trwl-demo-trip:trip:1';

    private const string MIDDLE_IDENTIFIER = 'trwl-demo-station:station:2';

    private Station $start;

    private Station $middle;

    private Station $end;

    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->start = $this->createStationWithIdentifier('Start Station', 48.87857, 2.360305, 'trwl-demo-station:station:1');
        $this->middle = $this->createStationWithIdentifier('Middle station', 48.87412, 2.5833807, self::MIDDLE_IDENTIFIER);
        $this->end = $this->createStationWithIdentifier('End Station', 48.95662, 2.8733082, 'trwl-demo-station:station:3');

        $this->trip = Trip::create([
            'trip_id' => self::TRIP_ID,
            'category' => HafasTravelType::REGIONAL,
            'number' => 'P',
            'linename' => 'P',
            'origin_id' => $this->start->id,
            'destination_id' => $this->end->id,
            'departure' => '2025-05-01T10:00:00Z',
            'arrival' => '2025-05-01T15:10:00Z',
            'source' => TripSource::TRANSITOUS,
        ]);

        // stopovers as they exist right after the check-in
        foreach ([
            [$this->start->id, '2025-05-01T10:00:00Z'],
            [$this->middle->id, '2025-05-01T11:05:00Z'],
            [$this->end->id, '2025-05-01T15:10:00Z'],
        ] as [$stationId, $plannedTime]) {
            Stopover::factory()->create([
                'trip_id' => self::TRIP_ID,
                'train_station_id' => $stationId,
                'arrival_planned' => $plannedTime,
                'departure_planned' => $plannedTime,
            ]);
        }
    }

    private function createStationWithIdentifier(string $name, float $latitude, float $longitude, string $identifier): Station
    {
        $station = Station::factory()->create(['name' => $name, 'latitude' => $latitude, 'longitude' => $longitude]);
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'identifier' => $identifier,
            'type' => StationIdentifierType::MOTIS,
            'origin' => DataProvider::TRANSITOUS->value,
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        return $station;
    }

    /**
     * @return array<string, mixed> the leg as delivered by the MOTIS trip endpoint, with real-time data
     */
    private function getLeg(): array
    {
        $journey = json_decode(file_get_contents(__DIR__ . '/_data/motis_leg.json'), true);
        $leg = $journey['legs'][0];
        $leg['realTime'] = true;

        return $leg;
    }

    private function refreshTrip(): void
    {
        new MotisHydrator()->parseLegToUpdateStopovers($this->getLeg(), $this->trip, DataProvider::TRANSITOUS);
    }

    public function test_refresh_without_station_changes_keeps_stopovers_stable(): void
    {
        $this->refreshTrip();

        $this->assertEquals(3, Stopover::where('trip_id', self::TRIP_ID)->count());
        $this->assertEquals(3, Station::count());

        $middleStopover = Stopover::where('trip_id', self::TRIP_ID)
            ->where('train_station_id', $this->middle->id)
            ->firstOrFail();
        $this->assertEquals('2025-05-01 11:00:00', $middleStopover->arrival_real?->toDateTimeString(), 'Real-time data must be written to the existing stopover');
    }

    public function test_refresh_after_moving_identifier_to_new_station_does_not_duplicate_stopover(): void
    {
        // admin merges duplicate stations: create the merge target and move the identifier over
        $mergeTarget = Station::create([
            'name' => 'Middle station',
            'latitude' => 48.87412,
            'longitude' => 2.5833807,
            'relevance' => 100,
        ]);
        StationIdentifier::where('identifier', self::MIDDLE_IDENTIFIER)->update(['station_id' => $mergeTarget->id]);

        $this->refreshTrip();

        $middleStopovers = Stopover::where('trip_id', self::TRIP_ID)
            ->where('departure_planned', '2025-05-01 11:05:00')
            ->get();

        $this->assertCount(1, $middleStopovers, 'The middle stop must not be duplicated after moving its identifier to another station');
        $this->assertEquals($mergeTarget->id, $middleStopovers->first()->train_station_id, 'The existing stopover must be moved to the merge target station');
        $this->assertEquals(3, Stopover::where('trip_id', self::TRIP_ID)->count());
    }

    public function test_refresh_recreates_missing_stopover(): void
    {
        Stopover::where('trip_id', self::TRIP_ID)
            ->where('train_station_id', $this->middle->id)
            ->delete();

        $this->refreshTrip();

        $middleStopovers = Stopover::where('trip_id', self::TRIP_ID)
            ->where('departure_planned', '2025-05-01 11:05:00')
            ->get();

        $this->assertCount(1, $middleStopovers);
        $this->assertEquals($this->middle->id, $middleStopovers->first()->train_station_id);
        $this->assertEquals(3, Stopover::where('trip_id', self::TRIP_ID)->count());
    }

    public function test_refresh_resolves_station_via_identifier_and_does_not_create_new_stations(): void
    {
        // admin cleans up the station name; the MOTIS identifier still points to this station
        $this->middle->update(['name' => 'Musterlingen Mitte']);

        $this->refreshTrip();

        $this->assertEquals(3, Station::count(), 'No new station may be created while the identifier still resolves to an existing station');
        $this->assertEquals(
            $this->middle->id,
            StationIdentifier::where('identifier', self::MIDDLE_IDENTIFIER)->first()->station_id,
            'The identifier must keep pointing to the renamed station'
        );
    }
}
