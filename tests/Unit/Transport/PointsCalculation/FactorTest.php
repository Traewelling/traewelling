<?php

namespace Tests\Unit\Transport\PointsCalculation;

use App\Enum\PointReasons;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;

class FactorTest
{
    public function testInTimeFactor(): void {
        $this->assertEquals(1, PointsCalculationController::getFactorByReason(PointReasons::IN_TIME));
    }

    public function testGoodEnoughFactor(): void {
        $this->assertEquals(0.25, PointsCalculationController::getFactorByReason(PointReasons::GOOD_ENOUGH));
    }

    public function testNotSufficientFactor(): void {
        $this->assertEquals(0, PointsCalculationController::getFactorByReason(PointReasons::NOT_SUFFICIENT));
    }

    public function testForcedFactor(): void {
        $this->assertEquals(0, PointsCalculationController::getFactorByReason(PointReasons::FORCED));
    }
}
