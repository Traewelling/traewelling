<?php

namespace Tests\Unit\Transport\PointsCalculation;

use App\Enum\PointReason;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use Tests\Unit\UnitTestCase;

class FactorTest extends UnitTestCase
{
    public static function factorDataProvider(): array {
        return [
            [PointReason::IN_TIME, 1],
            [PointReason::GOOD_ENOUGH, 0.25],
            [PointReason::NOT_SUFFICIENT, 0],
            [PointReason::FORCED, 0],
        ];
    }

    /**
     * @dataProvider factorDataProvider
     */
    public function testFactor(PointReason $reason, float $expectedFactor): void {
        $this->assertEquals($expectedFactor, PointsCalculationController::getFactorByReason($reason));
    }
}
