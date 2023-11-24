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

    public static function reasonDataProvider(): array {
        return [
            '5 Minutes before'  => [now()->addMinutes(5), now()->addHour(), false, now(), PointReason::IN_TIME],
            '25 Minutes before' => [now()->addMinutes(25), now()->addHour(), false, now(), PointReason::GOOD_ENOUGH],
            '65 Minutes before' => [now()->addMinutes(65), now()->addHour(), false, now(), PointReason::NOT_SUFFICIENT],
            'during trip'       => [now()->subHour(), now()->addHour(), false, now(), PointReason::IN_TIME],
            '11 Minutes after'  => [now()->subHour(), now(), false, now()->addMinutes(11), PointReason::GOOD_ENOUGH],
            '61 Minutes after'  => [now()->subHours(2), now()->subMinutes(61), false, now(), PointReason::NOT_SUFFICIENT],
            'forced'            => [now()->subHour(), now()->addHour(), true, now(), PointReason::FORCED],
        ];
    }

    /**
     * @dataProvider reasonDataProvider
     */
    public function testReason(
        Carbon      $departure,
        Carbon      $arrival,
        bool        $forceCheckin,
        Carbon      $timestampOfView,
        PointReason $expectedReason
    ): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       $departure,
            arrival:         $arrival,
            forceCheckin:    $forceCheckin,
            timestampOfView: $timestampOfView,
        );

        $this->assertEquals($expectedReason, $pointReason);
    }
}
