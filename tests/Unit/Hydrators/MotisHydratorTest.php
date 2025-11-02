<?php

declare(strict_types=1);

namespace Tests\Unit\Hydrators;

use App\DataProviders\Hydrators\MotisHydrator;
use App\DataProviders\Repositories\MotisLicenseRepository;
use App\DataProviders\Repositories\StationRepository;
use App\Enum\DataProvider as DataProviderEnum;
use App\Models\MotisSourceLicense;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Services\LicenseService;
use App\Services\OperatorService;
use App\StationIdentifierType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class MotisHydratorTest extends UnitTestCase
{
    public static function filterLicenseProvider(): array {
        $license         = new MotisSourceLicense();
        $license->active = true;

        return [
            'no license'   => [
                'expected' => 0,
                'license'  => null,
            ],
            'with license' => [
                'expected' => 3,
                'license'  => $license
            ],
        ];
    }

    private function getStations(): Collection {
        $stations = [
            [
                'id'                => '1',
                'name'              => 'Start Station',
                'stationIdentifier' => 'trwl-demo-station:station:1'
            ],
            [
                'id'                => '2',
                'name'              => 'End Station',
                'stationIdentifier' => 'trwl-demo-station:station:3'
            ],
            [
                'id'                => '3',
                'name'              => 'Middle Station',
                'stationIdentifier' => 'trwl-demo-station:station:2'
            ],
        ];
        return collect($stations)->map(function($data) {
            $station = Station::factory()
                              ->make([
                                         'name' => $data['name'],
                                         'ibnr' => $data['stationIdentifier'],
                                     ]);
            $station->setAttribute('id', $data['id']);
            $identifier = StationIdentifier::factory()->make([
                                                                 'identifier' => $data['stationIdentifier'],
                                                                 'type'       => StationIdentifierType::MOTIS,
                                                                 'origin'     => DataProviderEnum::TRANSITOUS->value,
                                                             ]);
            $station->setRelation(
                'stationIdentifiers',
                collect()->push($identifier)
            );
            return $station;
        });
    }

    private function getDepartures(): array {
        return json_decode(file_get_contents(__DIR__ . '/_data/motis_departures.json'), true);
    }

    #[DataProvider('filterLicenseProvider')]
    public function testMapDeparturesFilterLicense(int $expected, ?MotisSourceLicense $license): void {
        Config::set('trwl.motis.filter_licenses', true);

        $mockRepo = $this->getMockBuilder(MotisLicenseRepository::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['getLicense'])
                         ->getMock();
        $mockRepo->method('getLicense')
                 ->willReturn($license);

        $mockStationRepo = $this->getMockBuilder(StationRepository::class)
                                ->disableOriginalConstructor()
                                ->onlyMethods(['getStationsByIdentifiers'])
                                ->getMock();
        $mockStationRepo->method('getStationsByIdentifiers')
                        ->willReturn($this->getStations());

        $mockOperatorRepo = $this->getMockBuilder(OperatorService::class)
                                 ->disableOriginalConstructor()
                                 ->onlyMethods(['parseTransitousOperator'])
                                 ->getMock();
        $mockOperatorRepo->method('parseTransitousOperator')
                         ->willReturn(null);

        $mockLicenseService = $this->getMockBuilder(LicenseService::class)
                                   ->disableOriginalConstructor()
                                   ->onlyMethods(['getLicenseDataForSource'])
                                   ->getMock();
        $mockLicenseService->method('getLicenseDataForSource')
                           ->willReturn(null);


        $hydrator   = new MotisHydrator($mockRepo, $mockStationRepo, $mockOperatorRepo, $mockLicenseService);
        $departures = $hydrator->mapDepartures($this->getDepartures(), Station::factory()->makeOne(), DataProviderEnum::TRANSITOUS);

        $this->assertCount($expected, $departures->departures);

        $removedCount = count($this->getDepartures()) - $expected;
        $this->assertEquals($removedCount, $departures->removedCount);
    }

}
