<?php

namespace Tests\Unit\Enum;

use App\Enum\CacheKey;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class CacheKeyTest extends TestCase
{

    public function testGetGlobalStatsKey() {
        $from = Carbon::create(2012, 2, 12, 15, 32, 45);
        $to = $from->clone()->addQuarter();
        $second_to = $to->clone()->addMinutes(5);

        $expected = "StatisticsGlobal-from-2012-02-12-to-2012-05-12";
        self::assertEquals($expected, CacheKey::getGlobalStatsKey($from, $to));
        self::assertEquals($expected, CacheKey::getGlobalStatsKey($from, $second_to));
    }
}
