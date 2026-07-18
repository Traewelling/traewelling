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

    public function test_stations_of_excluded_sources_are_filtered_from_search(): void
    {
        config(['trwl.motis.excluded_sources' => ['de-amarillo-bw']]);

        Http::fake([
            '*/geocode*' => Http::response([
                [
                    'type' => 'STOP',
                    'id' => 'de-DELFI_de:03241:27',
                    'name' => 'Hannover Hbf',
                    'lat' => 52.376761,
                    'lon' => 9.741021,
                ],
                [
                    'type' => 'STOP',
                    'id' => 'de-amarillo-bw_de:08221:1160',
                    'name' => 'Heidelberg, Irgendwo',
                    'lat' => 49.409445,
                    'lon' => 8.692046,
                ],
            ]),
        ]);

        $stations = $this->dataProvider->getStations('Hannover');

        $this->assertCount(1, $stations, 'Only the non-excluded station may be returned');
        $this->assertSame('Hannover Hbf', $stations->first()->name);
    }
}
