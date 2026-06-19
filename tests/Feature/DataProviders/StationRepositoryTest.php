<?php

declare(strict_types=1);

namespace Tests\Feature\DataProviders;

use App\DataProviders\Repositories\StationRepository;
use App\Enum\DataProvider;
use App\Enum\StationIdentifierType;
use App\Models\Station;
use App\Models\StationIdentifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\FeatureTestCase;

class StationRepositoryTest extends FeatureTestCase
{
    use RefreshDatabase;

    private StationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StationRepository();
    }

    public function test_update_or_create_by_ifopt_backfills_ifopt_when_station_found_via_existing_ifopt(): void
    {
        $station = Station::factory()->create();
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'type' => StationIdentifierType::IFOPT,
            'identifier' => 'de:12345:67890',
        ]);

        $result = $this->repository->updateOrCreateByIfopt('de-DELFI_de:12345:67890', DataProvider::TRANSITOUS, 52.0, 13.0);

        $this->assertNotNull($result);
        $this->assertEquals($station->id, $result->id);

        $this->assertDatabaseHas('station_identifiers', [
            'station_id' => $station->id,
            'type' => 'motis',
            'identifier' => 'de-DELFI_de:12345:67890',
        ]);

        // IFOPT should still be there (already existed)
        $this->assertEquals(
            1,
            StationIdentifier::where('type', 'ifopt')->where('identifier', 'de:12345:67890')->count()
        );
    }

    public function test_update_or_create_by_ifopt_backfills_ifopt_when_station_found_via_delfi_motis(): void
    {
        $station = Station::factory()->create();
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'type' => StationIdentifierType::MOTIS,
            'identifier' => 'de-DELFI_de:12345:11111',
            'origin' => DataProvider::TRANSITOUS->value,
        ]);

        $result = $this->repository->updateOrCreateByIfopt('de-DELFI_de:12345:11111', DataProvider::TRANSITOUS);

        $this->assertNotNull($result);

        // IFOPT should now be backfilled
        $this->assertDatabaseHas('station_identifiers', [
            'station_id' => $station->id,
            'type' => 'ifopt',
            'identifier' => 'de:12345:11111',
        ]);
    }

    public function test_update_or_create_by_ifopt_stores_only_base_ifopt_not_quay_suffix(): void
    {
        $station = Station::factory()->create();
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'type' => StationIdentifierType::IFOPT,
            'identifier' => 'de:08212:314',
        ]);

        $this->repository->updateOrCreateByIfopt('de-DELFI_de:08212:314:1:1', DataProvider::TRANSITOUS);

        // Only base IFOPT should exist, not the quay-extended version
        $this->assertEquals(
            1,
            StationIdentifier::where('type', 'ifopt')->where('identifier', 'de:08212:314')->count()
        );
        $this->assertDatabaseMissing('station_identifiers', [
            'type' => 'ifopt',
            'identifier' => 'de:08212:314:1:1',
        ]);
    }

    public function test_create_motis_station_identifier_backfills_ifopt_for_delfi_stop(): void
    {
        $rawStation = [
            'stopId' => 'de-DELFI_de:02000:11907',
            'name' => 'Baumwall (U)',
            'lat' => 53.544193,
            'lon' => 9.981119,
            'areas' => [],
        ];

        $station = $this->repository->createMotisStationIdentifier($rawStation, DataProvider::TRANSITOUS);

        $this->assertDatabaseHas('station_identifiers', [
            'station_id' => $station->id,
            'type' => 'ifopt',
            'identifier' => 'de:02000:11907',
        ]);
    }

    public function test_create_motis_station_identifier_backfills_ifopt_for_austrian_pta_source(): void
    {
        $rawStation = [
            'stopId' => 'at-PTA-Styria-Flex-2026_at:49:1349:25',
            'name' => 'Wien Hbf',
            'lat' => 48.185,
            'lon' => 16.376,
            'areas' => [],
        ];

        $station = $this->repository->createMotisStationIdentifier($rawStation, DataProvider::TRANSITOUS);

        $this->assertDatabaseHas('station_identifiers', [
            'station_id' => $station->id,
            'type' => 'ifopt',
            'identifier' => 'at:49:1349',
        ]);
    }

    public function test_create_motis_station_identifier_backfills_base_ifopt_from_extended_identifier(): void
    {
        // Identifier with extra quay/access levels beyond the base IFOPT
        $rawStation = [
            'stopId' => 'at-Railway-Current-Reference-Data-2026_at:49:1349:0:4',
            'name' => 'Wien Hbf',
            'lat' => 48.185,
            'lon' => 16.376,
            'areas' => [],
        ];

        $station = $this->repository->createMotisStationIdentifier($rawStation, DataProvider::TRANSITOUS);

        // Only base IFOPT (3 parts) should be stored, not the extended version
        $this->assertDatabaseHas('station_identifiers', [
            'station_id' => $station->id,
            'type' => 'ifopt',
            'identifier' => 'at:49:1349',
        ]);
        $this->assertDatabaseMissing('station_identifiers', [
            'type' => 'ifopt',
            'identifier' => 'at:49:1349:0:4',
        ]);
    }

    public function test_create_motis_station_identifier_does_not_backfill_ifopt_for_non_ifopt_stop(): void
    {
        $rawStation = [
            'stopId' => 'trwl-demo-source:station:999',
            'name' => 'Some Station',
            'lat' => 52.0,
            'lon' => 13.0,
            'areas' => [],
        ];

        $station = $this->repository->createMotisStationIdentifier($rawStation, DataProvider::TRANSITOUS);

        $this->assertDatabaseMissing('station_identifiers', [
            'station_id' => $station->id,
            'type' => 'ifopt',
        ]);
    }

    public function test_ifopt_backfill_skips_and_logs_warning_when_ifopt_already_claimed_by_another_station(): void
    {
        $existingStation = Station::factory()->create();
        StationIdentifier::factory()->create([
            'station_id' => $existingStation->id,
            'type' => StationIdentifierType::IFOPT,
            'identifier' => 'de:99999:88888',
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->with('IFOPT identifier already assigned to a different station, skipping backfill', \Mockery::any());

        $rawStation = [
            'stopId' => 'de-DELFI_de:99999:88888',
            'name' => 'Other Station',
            'lat' => 48.0,
            'lon' => 11.0,
            'areas' => [],
        ];

        $this->repository->createMotisStationIdentifier($rawStation, DataProvider::TRANSITOUS);

        // Only one IFOPT record should exist
        $this->assertEquals(
            1,
            StationIdentifier::where('type', 'ifopt')->where('identifier', 'de:99999:88888')->count()
        );
        // It should still belong to the original station
        $this->assertDatabaseHas('station_identifiers', [
            'station_id' => $existingStation->id,
            'type' => 'ifopt',
            'identifier' => 'de:99999:88888',
        ]);
    }
}
