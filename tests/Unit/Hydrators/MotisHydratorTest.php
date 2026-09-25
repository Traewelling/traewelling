<?php

declare(strict_types=1);

namespace Tests\Unit\Hydrators;

use App\DataProviders\Hydrators\MotisHydrator;
use App\DataProviders\Repositories\MotisLicenseRepository;
use App\DataProviders\Repositories\StationRepository;
use App\Enum\DataProvider as DataProviderEnum;
use App\Enum\StationIdentifierType;
use App\Models\MotisSourceLicense;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Services\LicenseService;
use App\Services\OperatorService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class MotisHydratorTest extends UnitTestCase
{
    public static function filterLicenseProvider(): array
    {
        $license = new MotisSourceLicense();
        $license->active = true;

        return [
            'no license' => [
                // RT:0:0 entry always passes through, 3 GTFS entries are filtered
                'expected' => 1,
                'license' => null,
            ],
            'with license' => [
                // RT:0:0 entry always passes through + 3 GTFS entries with active license
                'expected' => 4,
                'license' => $license,
            ],
        ];
    }

    private function getStations(): Collection
    {
        $stations = [
            [
                'id' => '1',
                'name' => 'Start Station',
                'stationIdentifier' => 'trwl-demo-station:station:1',
            ],
            [
                'id' => '2',
                'name' => 'End Station',
                'stationIdentifier' => 'trwl-demo-station:station:3',
            ],
            [
                'id' => '3',
                'name' => 'Middle Station',
                'stationIdentifier' => 'trwl-demo-station:station:2',
            ],
        ];

        return collect($stations)->map(function ($data) {
            $station = Station::factory()
                ->make([
                    'name' => $data['name'],
                    'ibnr' => $data['stationIdentifier'],
                ]);
            $station->setAttribute('id', $data['id']);
            $identifier = StationIdentifier::factory()->make([
                'identifier' => $data['stationIdentifier'],
                'type' => StationIdentifierType::MOTIS,
                'origin' => DataProviderEnum::TRANSITOUS->value,
            ]);
            $station->setRelation(
                'stationIdentifiers',
                collect()->push($identifier)
            );

            return $station;
        });
    }

    private function getDepartures(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/_data/motis_departures.json'), true);
    }

    #[DataProvider('filterLicenseProvider')]
    public function test_map_departures_filter_license(int $expected, ?MotisSourceLicense $license): void
    {
        Config::set('trwl.motis.filter_licenses', true);

        $mockRepo = $this->getMockBuilder(MotisLicenseRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLicense'])
            ->getMock();
        $mockRepo->method('getLicense')
            ->willReturn($license);

        $mockStationRepo = $this->getMockBuilder(StationRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStationsByIdentifiers', 'retireMotisIdentifierIfReused'])
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

        $hydrator = new MotisHydrator($mockRepo, $mockStationRepo, $mockOperatorRepo, $mockLicenseService);
        $departures = $hydrator->mapDepartures($this->getDepartures(), Station::factory()->makeOne(), DataProviderEnum::TRANSITOUS);

        $this->assertCount($expected, $departures->departures);

        $removedCount = count($this->getDepartures()) - $expected;
        $this->assertEquals($removedCount, $departures->removedCount);
    }

    public function test_map_departures_strips_redundant_line_name_from_headsign(): void
    {
        Config::set('trwl.motis.filter_licenses', false);

        $cases = [
            // displayName, routeShortName, headsign, expected direction
            ['S1', 'S1', 'S1 Hochstetten', 'Hochstetten'],
            ['3', '3', '3 Daxlanden über Hbf', 'Daxlanden über Hbf'],
            ['RB44 (15922)', 'RB44', 'RB44 Bühl (Baden)', 'Bühl (Baden)'],
            ['S4', 'S4', 'Karlsruhe Albtalbahnhof', 'Karlsruhe Albtalbahnhof'],
            ['S1', 'S1', 'S11 Ittersbach', 'S11 Ittersbach'],
            ['S1', 'S1', 'S1', 'S1'],
            ['ICE 9577', null, '', ''],
        ];

        $template = $this->getDepartures()[0];
        $entries = array_map(static function (array $case) use ($template): array {
            [$displayName, $routeShortName, $headsign] = $case;

            return array_merge($template, array_filter([
                'displayName' => $displayName,
                'routeShortName' => $routeShortName,
                'headsign' => $headsign,
            ], static fn (?string $value): bool => $value !== null));
        }, $cases);

        $mockStationRepo = $this->getMockBuilder(StationRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStationsByIdentifiers', 'retireMotisIdentifierIfReused'])
            ->getMock();
        $mockStationRepo->method('getStationsByIdentifiers')
            ->willReturn($this->getStations());

        $mockOperatorRepo = $this->getMockBuilder(OperatorService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['parseTransitousOperator'])
            ->getMock();
        $mockOperatorRepo->method('parseTransitousOperator')
            ->willReturn(null);

        $hydrator = new MotisHydrator(stationRepository: $mockStationRepo, operatorService: $mockOperatorRepo);
        $departures = $hydrator->mapDepartures($entries, Station::factory()->makeOne(), DataProviderEnum::TRANSITOUS);

        $this->assertSame(
            array_column($cases, 3),
            $departures->departures->map(fn ($departure) => $departure->trip->direction)->all()
        );
    }
}
