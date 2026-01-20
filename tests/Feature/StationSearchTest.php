<?php

namespace Tests\Feature;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\Exceptions\DataProviderException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\FeatureTestCase;

class StationSearchTest extends FeatureTestCase
{
    use RefreshDatabase;

    private DataProviderInterface $dataProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataProvider = (new DataProviderBuilder())->build();
    }

    public function test_get_nearby_station_fails(): void
    {
        Http::fake(Http::response(status: 503));

        $this->assertThrows(function () {
            $this->dataProvider->getNearbyStations(
                self::HANNOVER_HBF['location']['latitude'],
                self::HANNOVER_HBF['location']['longitude']);
        }, DataProviderException::class);
    }
}
