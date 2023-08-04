<?php

namespace Tests\Unit\Enum;

use App\Enum\CacheKey;
use Carbon\Carbon;
use Tests\Unit\UnitTestCase;

class CacheKeyTest extends UnitTestCase
{

    public function testGetGlobalStatsKey(): void {
        $from      = Carbon::create(2012, 2, 12, 15, 32, 45);
        $to        = $from->clone()->addQuarter();
        $second_to = $to->clone()->addMinutes(5);

        $expected = "StatisticsGlobal-from-2012-02-12-to-2012-05-12";
        $this->assertEquals($expected, CacheKey::getGlobalStatsKey($from, $to));
        $this->assertEquals($expected, CacheKey::getGlobalStatsKey($from, $second_to));
    }
}
