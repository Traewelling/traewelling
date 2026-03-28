<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Enum\StationIdentifierType;
use App\Models\Checkin;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Trip;
use App\Models\User;
use App\Repositories\StationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\FeatureTestCase;

class StationRepositoryTest extends FeatureTestCase
{
    use RefreshDatabase;

    private StationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(StationRepository::class);
    }

    public function test_get_station_by_name_finds_matching_stations(): void
    {
        $station = Station::factory()->create(['name' => 'ZZZ Unique Testbahnhof']);
        Station::factory()->create(['name' => 'Some Other Station']);

        $results = $this->repository->getStationByName('ZZZ Unique Testbahnhof');

        $this->assertTrue($results->contains('id', $station->id));
    }

    public function test_get_stations_by_ril_identifier_finds_station(): void
    {
        $station = Station::factory()->create();
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'type' => StationIdentifierType::DE_DB_RIL100,
            'identifier' => 'ZZZTEST',
        ]);

        $results = $this->repository->getStationsByFuzzyRilIdentifier('ZZZTEST');

        $this->assertTrue($results->contains('id', $station->id));
    }

    public function test_get_latest_arrivals_for_user_returns_destination_stations_in_order(): void
    {
        $user = User::factory()->create();

        // Create two distinct destination stations and dedicated trips so the factory
        // cannot accidentally reuse the same station for both checkins.
        $stationA = Station::factory()->create();
        $stationB = Station::factory()->create();

        $tripA = Trip::factory()->create(['destination_id' => $stationA->id]);
        $tripB = Trip::factory()->create(['destination_id' => $stationB->id]);

        Checkin::factory()->create([
            'user_id' => $user->id,
            'trip_id' => $tripA->trip_id,
            'origin_stopover_id' => $tripA->stopovers->where('train_station_id', $tripA->origin_id)->first()->id,
            'destination_stopover_id' => $tripA->stopovers->where('train_station_id', $stationA->id)->first()->id,
            'arrival' => now()->subHours(2),
        ]);

        Checkin::factory()->create([
            'user_id' => $user->id,
            'trip_id' => $tripB->trip_id,
            'origin_stopover_id' => $tripB->stopovers->where('train_station_id', $tripB->origin_id)->first()->id,
            'destination_stopover_id' => $tripB->stopovers->where('train_station_id', $stationB->id)->first()->id,
            'arrival' => now()->subHour(),
        ]);

        $results = $this->repository->getLatestArrivalsForUser($user, 5);
        $resultIds = $results->pluck('id');

        $this->assertContains($stationA->id, $resultIds);
        $this->assertContains($stationB->id, $resultIds);
        // more recent arrival (stationB, subHour) must appear before older (stationA, subHours(2))
        $this->assertLessThan(
            $resultIds->search($stationA->id),
            $resultIds->search($stationB->id)
        );
    }

    public function test_get_stations_by_wikidata_id_returns_existing_station(): void
    {
        $station = Station::factory()->create();
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'type' => StationIdentifierType::WIKIDATA_ID,
            'identifier' => 'Q99999',
        ]);

        $results = $this->repository->getStationsByWikidataId('Q99999');

        $this->assertTrue($results->contains('id', $station->id));
    }

    public function test_get_stations_by_wikidata_id_returns_empty_when_rate_limited(): void
    {
        // exhaust the per-ID rate limit so no Wikidata import is attempted
        RateLimiter::hit('fetch-wikidata-qid:Q00001', 5 * 60);

        $results = $this->repository->getStationsByWikidataId('Q00001');

        $this->assertEmpty($results);
    }

    public function test_get_station_by_identifier_returns_matching_station(): void
    {
        $station = Station::factory()->create();
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'type' => StationIdentifierType::MOTIS,
            'identifier' => 'test-motis-id',
        ]);

        $result = $this->repository->getStationByIdentifier('test-motis-id', StationIdentifierType::MOTIS);

        $this->assertNotNull($result);
        $this->assertEquals($station->id, $result->id);
    }

    public function test_get_station_by_identifier_returns_null_when_not_found(): void
    {
        $result = $this->repository->getStationByIdentifier('nonexistent', StationIdentifierType::MOTIS);

        $this->assertNull($result);
    }

    public function test_get_station_by_ibnr_delegates_to_identifier_lookup(): void
    {
        $station = Station::factory()->create();
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'type' => StationIdentifierType::DE_DB_IBNR,
            'identifier' => '8000105',
        ]);

        $result = $this->repository->getStationByIbnr('8000105');

        $this->assertNotNull($result);
        $this->assertEquals($station->id, $result->id);
    }

    public function test_get_by_identifier_returns_matching_station(): void
    {
        $station = Station::factory()->create();
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'type' => StationIdentifierType::MOTIS,
            'identifier' => 'unique-identifier-xyz',
        ]);

        $result = $this->repository->getByIdentifier('unique-identifier-xyz', StationIdentifierType::MOTIS);

        $this->assertNotNull($result);
        $this->assertEquals($station->id, $result->id);
    }

    public function test_get_by_id_returns_matching_station(): void
    {
        $station = Station::factory()->create();

        $result = $this->repository->getById($station->id);

        $this->assertNotNull($result);
        $this->assertEquals($station->id, $result->id);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getById(PHP_INT_MAX);

        $this->assertNull($result);
    }
}
