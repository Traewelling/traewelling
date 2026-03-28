<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Checkin;

use App\DataProviders\DataProviderInterface;
use App\Models\Station;
use App\Repositories\StationRepository;
use App\Services\Checkin\StationService;
use Mockery;
use Mockery\MockInterface;
use Tests\Unit\UnitTestCase;

class StationServiceTest extends UnitTestCase
{
    private MockInterface $repo;

    private MockInterface $dataProvider;

    private StationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(StationRepository::class);
        $this->dataProvider = Mockery::mock(DataProviderInterface::class);
        $this->service = new StationService($this->repo, $this->dataProvider);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_search_with_ril100_returns_from_repository_if_found(): void
    {
        $stations = collect([$this->makeStation(1, 'Hannover Hbf')]);

        $this->repo->shouldReceive('getStationsByFuzzyRilIdentifier')->once()->with('HH')->andReturn($stations);
        $this->dataProvider->shouldNotReceive('getStations');

        $result = $this->service->search('HH');

        $this->assertCount(1, $result);
        $this->assertSame($stations->first(), $result->first());
    }

    public function test_search_with_ril100_falls_through_to_provider_when_empty(): void
    {
        $this->repo->shouldReceive('getStationsByFuzzyRilIdentifier')->once()->andReturn(collect());
        $this->dataProvider->shouldReceive('getStations')->once()->with('HH')->andReturn(collect());
        $this->repo->shouldReceive('getStationByName')->once()->andReturn(collect());

        $result = $this->service->search('HH');

        $this->assertCount(0, $result);
    }

    public function test_search_with_wikidata_id_delegates_to_repository(): void
    {
        $stations = collect([$this->makeStation(42, 'Berlin Hbf')]);

        $this->repo->shouldReceive('getStationsByWikidataId')->once()->with('Q12345')->andReturn($stations);
        $this->dataProvider->shouldNotReceive('getStations');
        $this->repo->shouldNotReceive('getStationByName');

        $result = $this->service->search('Q12345');

        $this->assertCount(1, $result);
    }

    public function test_search_merges_provider_and_db_results_when_under_ten(): void
    {
        $providerStations = collect([
            $this->makeStation(1, 'Station A'),
            $this->makeStation(2, 'Station B'),
            $this->makeStation(3, 'Station C'),
        ]);
        $dbStations = collect([
            $this->makeStation(4, 'Station D'),
            $this->makeStation(5, 'Station E'),
        ]);

        $this->dataProvider->shouldReceive('getStations')->once()->andReturn($providerStations);
        $this->repo->shouldReceive('getStationByName')->once()->andReturn($dbStations);

        $result = $this->service->search('Station');

        $this->assertCount(5, $result);
    }

    public function test_search_skips_db_when_provider_returns_ten_or_more(): void
    {
        $providerStations = collect(array_map(
            fn (int $i) => $this->makeStation($i, "Station $i"),
            range(1, 10)
        ));

        $this->dataProvider->shouldReceive('getStations')->once()->andReturn($providerStations);
        $this->repo->shouldNotReceive('getStationByName');

        $result = $this->service->search('Station');

        $this->assertCount(10, $result);
    }

    public function test_search_deduplicates_results_between_provider_and_db(): void
    {
        $sharedStation = $this->makeStation(1, 'Shared Station');
        $exclusiveStation = $this->makeStation(2, 'DB Only Station');

        $this->dataProvider->shouldReceive('getStations')->once()->andReturn(collect([$sharedStation]));
        // DB returns both the shared station (duplicate) and one exclusive station
        $this->repo->shouldReceive('getStationByName')->once()->andReturn(collect([$sharedStation, $exclusiveStation]));

        $result = $this->service->search('Station');

        $this->assertCount(2, $result);
        $this->assertCount(1, $result->where('id', 1), 'shared station must appear only once');
    }

    private function makeStation(int $id, string $name): Station
    {
        $station = new Station();
        $station->forceFill(['id' => $id, 'name' => $name]);

        return $station;
    }
}
