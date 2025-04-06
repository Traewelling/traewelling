<?php

namespace Tests\Unit\Helpers;


use App\Helpers\Formatter;
use Tests\Unit\UnitTestCase;

class FormatterTest extends UnitTestCase
{
    public static function stationNameProvider(): array {
        return [
            ['Halle (Saale) Central Station (FlixTrain)', 'HALLESAALEHBF'],
            ['Bad Hersfeld (FlixTrain)', 'BADHERSFELD'],
            ['Frankfurt (Main) Süd (FlixTrain)', 'FRANKFURTMAINSD'],
            ['Hauptfriedhof, Südeingang, Musterstadt', 'HBFFRIEDHOFSDEINGANG'],
            ['Bahnhofsvorplatz, Musterstadt', 'SVORPLATZ'],
            ['Praha Hl.n', 'PRAHAHBF'],
            ['Berlin Hbf (tief)', 'BERLINHBF'],
            ['Stuttgart Hbf (oben)', 'STUTTGARTHBF'],
            ['Tieflehn (tief)', 'LEHN'],
            ['München Hbf Gl.27-36', 'MNCHENHBF'],
            ['Albtalbahnhof', 'ALBTAL'],
        ];
    }

    /**
     * @dataProvider stationNameProvider
     */
    public function testSimplifyStationName($stationName, $expected) {
        $this->assertEquals($expected, Formatter::simplifyStationName($stationName));
    }
}
