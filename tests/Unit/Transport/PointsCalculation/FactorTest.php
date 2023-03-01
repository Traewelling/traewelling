<?php

namespace Tests\Unit\Transport\PointsCalculation;

use App\Enum\PointReason;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use Tests\TestCase;

class FactorTest extends TestCase
{
    public function testInTimeFactor(): void {
        $this->assertEquals(1, PointsCalculationController::getFactorByReason(PointReason::IN_TIME));
    }

    public function testGoodEnoughFactor(): void {
        $this->assertEquals(0.25, PointsCalculationController::getFactorByReason(PointReason::GOOD_ENOUGH));
    }

    public function testNotSufficientFactor(): void {
        $this->assertEquals(0, PointsCalculationController::getFactorByReason(PointReason::NOT_SUFFICIENT));
    }

    public function testForcedFactor(): void {
        $this->assertEquals(0, PointsCalculationController::getFactorByReason(PointReason::FORCED));
    }
}
