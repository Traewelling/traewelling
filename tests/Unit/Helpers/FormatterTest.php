<?php

namespace Tests\Unit\Helpers;


use App\Helpers\Formatter;
use Tests\Unit\UnitTestCase;

class FormatterTest extends UnitTestCase
{
    public static function stationNameProvider(): array {
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

    /**
     * @dataProvider stationNameProvider
     */
    public function testSimplifyStationName($stationName, $city, $expected) {
        $this->assertEquals($expected, Formatter::simplifyStationName($stationName, $city));
    }
}
