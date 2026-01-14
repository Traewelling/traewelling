<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Formatter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class FormatterTest extends UnitTestCase
{
    public static function stationNameProvider(): array
    {
        return [
            ['Halle (Saale) Central Station (FlixTrain)', null, 'HALLESAALEHBF'],
            ['Bad Hersfeld (FlixTrain)', null, 'BADHERSFELD'],
            ['Frankfurt (Main) Süd (FlixTrain)', null, 'FRANKFURTMAINSD'],
            ['Hauptfriedhof, Südeingang, Musterstadt', null, 'HBFFRIEDHOFSDEINGANGMUSTERSTADT'],
            ['Bahnhofsvorplatz, Musterstadt', null, 'SVORPLATZMUSTERSTADT'],
            ['Praha Hl.n', null, 'PRAHAHBF'],
            ['Berlin Hbf (tief)', null, 'BERLINHBF'],
            ['Stuttgart Hbf (oben)', null, 'STUTTGARTHBF'],
            ['Tieflehn (tief)', null, 'LEHN'],
            ['München Hbf Gl.27-36', null, 'MNCHENHBF'],
            ['Albtalbahnhof', null, 'ALBTAL'],
            ['Karlsruhe Hbf', 'Karlsruhe', 'HBF'],
            ['Karlsruher Platz, München', 'München', 'KARLSRUHERPLATZ'],
            ['München, Karlsruher Platz', 'München', 'KARLSRUHERPLATZ'],
            ['Hauptfriedhof, Südeingang, Musterstadt', 'musterstAdt', 'HBFFRIEDHOFSDEINGANG'],
            ['Bahnhofsvorplatz, Musterstadt', 'Musterstadt', 'SVORPLATZ'],
        ];
    }

    #[DataProvider('stationNameProvider')]
    public function test_simplify_station_name($stationName, $city, $expected)
    {
        $this->assertEquals($expected, Formatter::simplifyStationName($stationName, $city));
    }

    public static function stationMatchingProvider(): array
    {
        return [
            ['Karlsruhe Hbf', 'Karlsruhe Hbf', 'Karlsruhe', true],
            ['Karlsruhe Hbf', 'Karlsruhe Hbf', 'Karlsruhe', true],
            ['Regierungsplatz, Landshut', 'Landshut, Regierungsplatz', 'Landshut', true],
            ['Karlsruhe Hauptfriedhof', 'Hauptfriedhof', 'Karlsruhe', true],
            ['Karlsruhe, Stuttgarter Straße', 'Stuttgart, Karlsruher Straße', 'Stuttgart', false],
            ['Füttererstraße', 'Altdorfer Straße', '', false],
            ['Landshut, Füttererstraße', 'Landshut, Altdorfer Straße', 'Landshut', false],
        ];
    }

    #[DataProvider('stationMatchingProvider')]
    public function test_mapping($dbStation, $motisStation, $city, $match)
    {
        $dbSimplified = Formatter::simplifyStationName($dbStation, $city);
        $motisSimplified = Formatter::simplifyStationName($motisStation, $city);

        similar_text($dbSimplified, $motisSimplified, $percent);
        $this->assertEquals($match, $percent > 90);
    }

    public static function appendStationProvider(): array
    {
        return [
            ['Karlsruhe Hbf', 'Karlsruhe', 'Karlsruhe Hbf'],
            ['Tullastraße', 'Karlsruhe', 'Tullastraße, Karlsruhe'],
            ['Karlsruhe, Tullastraße', 'Karlsruhe', 'Karlsruhe, Tullastraße'],
            ['Karlsruhe Hbf', null, 'Karlsruhe Hbf'],
            ['Tullastraße', 'München', 'Tullastraße, München'],
            ['Karlsruhe, Tullastraße', 'München', 'Karlsruhe, Tullastraße, München'], // lol
            ['Karlsruhe Hbf', null, 'Karlsruhe Hbf'],
            ['Tullastraße', null, 'Tullastraße'],
            ['Karlsruhe, Tullastraße', null, 'Karlsruhe, Tullastraße'],
        ];
    }

    #[DataProvider('appendStationProvider')]
    public function test_append_station($stationName, $city, $expected)
    {
        $this->assertEquals($expected, Formatter::cityStationName($stationName, $city));
    }
}
