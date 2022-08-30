<?php

namespace Tests\Feature\Transport\PointsCalculation;

use App\Enum\PointReason;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use Carbon\Carbon;
use Tests\TestCase;

class ReasonTest extends TestCase
{

    protected function setUp(): void {
        Carbon::setTestNow("10.05.2020 13:15");
    }

    public function test5MinutesBeforeTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       Carbon::now()->addMinutes(5),
            arrival:         Carbon::now()->addHour(),
            forceCheckin:    false,
            timestampOfView: Carbon::now(),
        );

        $this->assertEquals(PointReason::IN_TIME, $pointReason);
    }

    public function test25MinutesBeforeTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       Carbon::now()->addMinutes(25),
            arrival:         Carbon::now()->addHour(),
            forceCheckin:    false,
            timestampOfView: Carbon::now(),
        );

        $this->assertEquals(PointReason::GOOD_ENOUGH, $pointReason);
    }

    public function test65MinutesBeforeTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       Carbon::now()->addMinutes(65),
            arrival:         Carbon::now()->addHour(),
            forceCheckin:    false,
            timestampOfView: Carbon::now(),
        );

        $this->assertEquals(PointReason::NOT_SUFFICIENT, $pointReason);
    }

    /**
     * Test if the reason IN_TIME is issued if the checkin is created in between departure and arrival
     */
    public function testEnRoute(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       Carbon::now()->subHour(),
            arrival:         Carbon::now()->addHour(),
            forceCheckin:    false,
            timestampOfView: Carbon::now(),
        );

        $this->assertEquals(PointReason::IN_TIME, $pointReason);
    }

    public function test1MinuteAfterTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       Carbon::now()->subHour(),
            arrival:         Carbon::now()->subMinute(),
            forceCheckin:    false,
            timestampOfView: Carbon::now(),
        );

        $this->assertEquals(PointReason::GOOD_ENOUGH, $pointReason);
    }

    public function test61MinuteAfterTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       Carbon::now()->subHours(2),
            arrival:         Carbon::now()->subMinutes(61),
            forceCheckin:    false,
            timestampOfView: Carbon::now(),
        );

        $this->assertEquals(PointReason::NOT_SUFFICIENT, $pointReason);
    }

    public function testForced(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       Carbon::now()->subHour(),
            arrival:         Carbon::now()->addHour(),
            forceCheckin:    true,
            timestampOfView: Carbon::now(),
        );

        $this->assertEquals(PointReason::FORCED, $pointReason);
    }
}
