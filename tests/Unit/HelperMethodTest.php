<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\App;

class HelperMethodTest extends UnitTestCase
{

    public function testNumberFunction(): void {
        App::setLocale('de');

        // Thousands with a rounding decimal
        $this->assertEquals("1.000,23", number(1000.234));

        // Deal with small numbers
        $this->assertEquals("0,00", number(0));
        $this->assertEquals("0,00", number(0.001));
        $this->assertEquals("0,01", number(0.0099));

        // if n \in \N, add dummy nulls after the null.
        $this->assertEquals("1,00", number(1));

        // What about large numbers?
        $this->assertEquals("4.294.967.296,00", number(2 ** 32));

        // What about negative numbers?
        $this->assertEquals("0,00", number(-0));
        $this->assertEquals("-1,00", number(-1));
        $this->assertEquals("-4.294.967.296,00", number(-1 * 2 ** 32));
    }

    public function test_secondsToDuration_and_durationToSpan(): void {
        $testcases = [
            0            => "0<small>min</small>",
            45           => "0<small>min</small>",
            60           => "1<small>min</small>",
            61           => "1<small>min</small>",
            5 * 60       => "5<small>min</small>",
            30 * 60      => "30<small>min</small>",
            60 * 60      => "1<small>h</small>&nbsp;0<small>min</small>",
            66 * 60      => "1<small>h</small>&nbsp;6<small>min</small>",
            2 * 60 * 60  => "2<small>h</small>&nbsp;0<small>min</small>",
            24 * 60 * 60 => "24<small>h</small>&nbsp;0<small>min</small>",
            25 * 60 * 60 => "25<small>h</small>&nbsp;0<small>min</small>",
        ];

        foreach ($testcases as $input => $output) {
            $this->assertEquals($output, durationToSpan(\secondsToDuration($input)));
        }
    }
}
