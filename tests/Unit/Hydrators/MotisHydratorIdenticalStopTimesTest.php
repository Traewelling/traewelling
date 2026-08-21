<?php

declare(strict_types=1);

namespace Tests\Unit\Hydrators;

use App\DataProviders\Hydrators\MotisHydrator;
use App\DataProviders\Repositories\TripRepository;
use App\Enum\DataProvider;
use App\Enum\HafasTravelType;
use App\Enum\StationIdentifierType;
use App\Enum\TripSource;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\Unit\UnitTestCase;

class MotisHydratorIdenticalStopTimesTest extends UnitTestCase
{
    use RefreshDatabase;

    private const string TRIP_ID = '20260821_17:57_de-DELFI_3281344415';

    private const string MOOSFENN_STOP_ID = 'de-DELFI_de:12054:900230070::2';

    private const string REHBRUECKE_STOP_ID = 'de-DELFI_de:12069:900220008::2';

    private array $stations = [];

    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ($this->getRawStops() as $rawStop) {
            $this->stations[$rawStop['stopId']] = $this->createStationWithIdentifier($rawStop);
        }

        $leg = $this->getLeg();
        $this->trip = Trip::create([
            'trip_id' => self::TRIP_ID,
            'category' => HafasTravelType::TRAM,
            'number' => $leg['displayName'],
            'linename' => $leg['displayName'],
            'origin_id' => $this->stations[$leg['from']['stopId']]->id,
            'destination_id' => $this->stations[$leg['to']['stopId']]->id,
            'departure' => $leg['from']['scheduledDeparture'],
            'arrival' => $leg['to']['scheduledArrival'],
            'source' => TripSource::TRANSITOUS,
        ]);
    }

    private function getLeg(): array
    {
        $journey = json_decode(file_get_contents(__DIR__ . '/_data/motis_leg_potsdam_tram_93.json'), true);

        return $journey['legs'][0];
    }

    private function getRawStops(): array
    {
        $leg = $this->getLeg();

        return [...$leg['intermediateStops'], $leg['from'], $leg['to']];
    }

    private function createStationWithIdentifier(array $rawStop): Station
    {
        $station = Station::factory()->create([
            'name' => $rawStop['name'],
            'latitude' => $rawStop['lat'],
            'longitude' => $rawStop['lon'],
        ]);
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'identifier' => $rawStop['stopId'],
            'type' => StationIdentifierType::MOTIS,
            'origin' => DataProvider::TRANSITOUS->value,
            'name' => $rawStop['name'],
            'latitude' => $rawStop['lat'],
            'longitude' => $rawStop['lon'],
        ]);

        return $station;
    }

    private function createExistingStopovers(array $withoutStopIds = []): void
    {
        foreach ($this->getRawStops() as $rawStop) {
            if (in_array($rawStop['stopId'], $withoutStopIds, true)) {
                continue;
            }

            $plannedArrival = $rawStop['scheduledArrival'] ?? $rawStop['scheduledDeparture'];
            $plannedDeparture = $rawStop['scheduledDeparture'] ?? $rawStop['scheduledArrival'];

            Stopover::factory()->create([
                'trip_id' => self::TRIP_ID,
                'train_station_id' => $this->stations[$rawStop['stopId']]->id,
                'arrival_planned' => $plannedArrival,
                'departure_planned' => $plannedDeparture,
                'arrival_real' => null,
                'departure_real' => null,
            ]);
        }
    }

    private function refreshTrip(): void
    {
        new MotisHydrator()->parseLegToUpdateStopovers($this->getLeg(), $this->trip, DataProvider::TRANSITOUS);
    }

    private function getStopoverStationNames(): Collection
    {
        return Stopover::with('station')
            ->where('trip_id', self::TRIP_ID)
            ->orderBy('departure_planned')
            ->orderBy('id')
            ->get()
            ->map(fn (Stopover $stopover) => $stopover->station->name);
    }

    private function getExpectedStationNames(): array
    {
        $leg = $this->getLeg();

        return array_column([$leg['from'], ...$leg['intermediateStops'], $leg['to']], 'name');
    }

    public function test_creating_a_trip_keeps_all_stops_with_identical_planned_times(): void
    {
        new TripRepository()->tryToSaveStopovers(
            $this->trip,
            new MotisHydrator()->parseLegToNewStopovers($this->getLeg(), DataProvider::TRANSITOUS)
        );

        $this->assertEqualsCanonicalizing(
            $this->getExpectedStationNames(),
            $this->getStopoverStationNames()->all(),
            'Stops sharing a planned time must not overwrite each other'
        );
    }

    public function test_refreshing_a_trip_keeps_all_stops_and_writes_real_time_data(): void
    {
        $this->createExistingStopovers();

        $this->refreshTrip();

        $this->assertEqualsCanonicalizing($this->getExpectedStationNames(), $this->getStopoverStationNames()->all());

        $moosfenn = Stopover::where('trip_id', self::TRIP_ID)
            ->where('train_station_id', $this->stations[self::MOOSFENN_STOP_ID]->id)
            ->firstOrFail();
        $this->assertEquals('2026-08-21 15:58:00', $moosfenn->departure_real?->toDateTimeString());
    }

    public function test_refresh_recreates_stops_that_were_previously_overwritten(): void
    {
        $this->createExistingStopovers(withoutStopIds: [
            self::MOOSFENN_STOP_ID,
            'de-DELFI_de:12054:900230067::2', // Potsdam, Kunersdorfer Str.
        ]);

        $this->refreshTrip();

        $this->assertEqualsCanonicalizing(
            $this->getExpectedStationNames(),
            $this->getStopoverStationNames()->all(),
            'The overwritten stops must be recreated on the next refresh'
        );
    }

    public function test_refresh_after_station_merge_moves_only_the_merged_stop(): void
    {
        $this->createExistingStopovers();
        $rehbruecke = $this->stations[self::REHBRUECKE_STOP_ID];
        $rehbrueckeStopoverId = Stopover::where('trip_id', self::TRIP_ID)
            ->where('train_station_id', $rehbruecke->id)
            ->firstOrFail()->id;

        $mergeTarget = Station::factory()->create([
            'name' => 'Potsdam, Am Moosfenn',
            'latitude' => 52.36348,
            'longitude' => 13.095061,
        ]);
        StationIdentifier::where('identifier', self::MOOSFENN_STOP_ID)->update(['station_id' => $mergeTarget->id]);

        $this->refreshTrip();

        $this->assertEqualsCanonicalizing(
            $this->getExpectedStationNames(),
            $this->getStopoverStationNames()->all(),
            'The merged stop must be moved, not duplicated'
        );
        $this->assertEquals(
            $mergeTarget->id,
            Stopover::where('trip_id', self::TRIP_ID)
                ->where('departure_planned', '2026-08-21 15:57:00')
                ->where('train_station_id', $mergeTarget->id)
                ->firstOrFail()->train_station_id
        );
        $this->assertEquals(
            $rehbruecke->id,
            Stopover::findOrFail($rehbrueckeStopoverId)->train_station_id,
            'The stop sharing the planned time must keep its station'
        );
    }

    public function test_checkin_can_resolve_origin_and_destination_of_stops_sharing_a_planned_time(): void
    {
        $this->createExistingStopovers(withoutStopIds: [self::MOOSFENN_STOP_ID]);

        $this->refreshTrip();

        $this->trip->load('stopovers');
        $origin = $this->trip->stopovers
            ->where('train_station_id', $this->stations[self::MOOSFENN_STOP_ID]->id)
            ->where('departure_planned', '2026-08-21 15:57:00')
            ->first();
        $destination = $this->trip->stopovers
            ->where('train_station_id', $this->stations['de-DELFI_de:12054:900230066::2']->id) // Sporthalle
            ->where('arrival_planned', '2026-08-21 16:03:00')
            ->first();

        $this->assertNotNull($origin, 'Check-in origin "Am Moosfenn" must be found on the trip');
        $this->assertNotNull($destination, 'Check-in destination "Sporthalle" must be found on the trip');
    }
}
