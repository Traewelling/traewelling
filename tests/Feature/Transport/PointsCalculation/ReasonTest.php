<?php

namespace Tests\Feature\Transport\PointsCalculation;

use App\Enum\HafasTravelType;
use App\Enum\PointReason;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReasonTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        Carbon::setTestNow("10.05.2020 13:15");
    }

    public function test5MinutesBeforeTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       now()->addMinutes(5),
            arrival:         now()->addHour(),
            forceCheckin:    false,
            timestampOfView: now(),
        );

        $this->assertEquals(PointReason::IN_TIME, $pointReason);
    }

    public function test25MinutesBeforeTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       now()->addMinutes(25),
            arrival:         now()->addHour(),
            forceCheckin:    false,
            timestampOfView: now(),
        );

        $this->assertEquals(PointReason::GOOD_ENOUGH, $pointReason);
    }

    public function test65MinutesBeforeTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       now()->addMinutes(65),
            arrival:         now()->addHour(),
            forceCheckin:    false,
            timestampOfView: now(),
        );

        $this->assertEquals(PointReason::NOT_SUFFICIENT, $pointReason);
    }

    /**
     * Test if the reason IN_TIME is issued if the checkin is created in between departure and arrival
     */
    public function testEnRoute(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       now()->subHour(),
            arrival:         now()->addHour(),
            forceCheckin:    false,
            timestampOfView: now(),
        );

        $this->assertEquals(PointReason::IN_TIME, $pointReason);
    }

    public function test11MinutesAfterTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       now()->subHour(),
            arrival:         now(),
            forceCheckin:    false,
            timestampOfView: now()->addMinutes(11),
        );

        $this->assertEquals(PointReason::GOOD_ENOUGH, $pointReason);
    }

    public function test61MinuteAfterTrip(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       now()->subHours(2),
            arrival:         now()->subMinutes(61),
            forceCheckin:    false,
            timestampOfView: now(),
        );

        $this->assertEquals(PointReason::NOT_SUFFICIENT, $pointReason);
    }

    public function testForced(): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       now()->subHour(),
            arrival:         now()->addHour(),
            forceCheckin:    true,
            timestampOfView: now(),
        );

        $this->assertEquals(PointReason::FORCED, $pointReason);
    }
}
