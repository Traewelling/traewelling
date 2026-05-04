<?php

declare(strict_types=1);

namespace Tests\Unit\DataProviders;

use App\DataProviders\Repositories\MotisLicenseRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class MotisLicenseRepositoryTest extends UnitTestCase
{
    public static function sourceStringProvider(): array
    {
        return [
            'gtfs without zip' => [
                'it_Veneto-Venezia-Bus.gtfs',
                'it',
                'it_Veneto-Venezia-Bus.gtfs',
            ],
            'gtfs with zip and path' => [
                'de_ALLOWED.gtfs.zip/stop_times.txt:1221005:1221019',
                'de',
                'de_ALLOWED.gtfs',
            ],
            'gtfs zip only' => [
                'eu_flixbus.gtfs.zip',
                'eu',
                'eu_flixbus.gtfs',
            ],
            'netex with zip and path' => [
                'it_trenitalia.netex.zip/trenitalia.xml:399512111:399512111',
                'it',
                'it_trenitalia.netex',
            ],
            'netex zip only' => [
                'it_trenitalia.netex.zip',
                'it',
                'it_trenitalia.netex',
            ],
            'gtfs with subdivision country code and path' => [
                'us-ca_sfmta.gtfs/agency:0',
                'us-ca',
                'us-ca_sfmta.gtfs',
            ],
            'gtfs with subdivision country code and hyphenated name' => [
                'us-ca_511-sfmarin.gtfs/route:6514',
                'us-ca',
                'us-ca_511-sfmarin.gtfs',
            ],
            'gtfs zip with subdivision country code' => [
                'au-nsw_Transport-for-New-South-Wales-GSBC007.gtfs.zip',
                'au-nsw',
                'au-nsw_Transport-for-New-South-Wales-GSBC007.gtfs',
            ],
            'unknown format returns empty' => [
                'something_without_known_extension',
                '',
                '',
            ],
            'empty string returns empty' => [
                '',
                '',
                '',
            ],
        ];
    }

    #[DataProvider('sourceStringProvider')]
    public function test_get_country_and_license(string $source, string $expectedCountry, string $expectedName): void
    {
        $repo = new MotisLicenseRepository();
        [$country, $name] = $repo->getCountryAndLicense($source);

        $this->assertEquals($expectedCountry, $country);
        $this->assertEquals($expectedName, $name);
    }
}
