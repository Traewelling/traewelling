<?php

namespace Unit\Transport;

use App\Enum\HafasTravelType;
use App\Enum\PointReason;
use App\Enum\TripSource;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use Carbon\Carbon;
use Tests\Unit\UnitTestCase;

class PointsCalculationTest extends UnitTestCase
{
    public static function reasonDataProvider(): array {
        return [
            '5 Minutes before'    => [now()->addMinutes(5), now()->addHour(), false, now(), TripSource::HAFAS, PointReason::IN_TIME],
            '25 Minutes before'   => [now()->addMinutes(25), now()->addHour(), false, now(), TripSource::HAFAS, PointReason::GOOD_ENOUGH],
            '65 Minutes before'   => [now()->addMinutes(65), now()->addHour(), false, now(), TripSource::HAFAS, PointReason::NOT_SUFFICIENT],
            'during trip'         => [now()->subHour(), now()->addHour(), false, now(), TripSource::HAFAS, PointReason::IN_TIME],
            '11 Minutes after'    => [now()->subHour(), now(), false, now()->addMinutes(11), TripSource::HAFAS, PointReason::GOOD_ENOUGH],
            '61 Minutes after'    => [now()->subHours(2), now()->subMinutes(61), false, now(), TripSource::HAFAS, PointReason::NOT_SUFFICIENT],
            'forced'              => [now()->subHour(), now()->addHour(), true, now(), TripSource::HAFAS, PointReason::FORCED],
            'manual created trip' => [now()->subHour(), now()->addHour(), false, now(), TripSource::USER, PointReason::MANUAL_TRIP],
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
        TripSource  $tripSource,
        PointReason $expectedReason
    ): void {
        $pointReason = PointsCalculationController::getReason(
            departure:       $departure,
            arrival:         $arrival,
            forceCheckin:    $forceCheckin,
            tripSource:      $tripSource,
            timestampOfView: $timestampOfView,
        );

        $this->assertEquals($expectedReason, $pointReason);
    }

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


    public static function calculatePointsDataProvider(): array {
        return [
            '50km in an IC/ICE => 50/10 + 10 = 15 points'                      => [
                15, HafasTravelType::NATIONAL_EXPRESS, now()->subMinutes(2), now()->addMinutes(10), TripSource::HAFAS
            ],
            '50km in an RB => 50/10 + 6 = 11 points'                           => [
                11, HafasTravelType::REGIONAL, now()->subMinutes(2), now()->addMinutes(10), TripSource::HAFAS
            ],
            '18km in a Bus => 20/10 + 2 = 4 points'                            => [
                7, HafasTravelType::BUS, now()->subMinutes(2), now()->addMinutes(10), TripSource::HAFAS
            ],
            '< 20min before 50/10 + 10 = 15'                                   => [
                15, HafasTravelType::NATIONAL_EXPRESS, now()->addMinutes(18), now()->addMinutes(40), TripSource::HAFAS
            ],
            '< 60min before, but > 20min (50/10 + 10) * 0.25 = 4'              => [
                4, HafasTravelType::NATIONAL_EXPRESS, now()->addMinutes(40), now()->addMinutes(100), TripSource::HAFAS
            ],
            '> 60min before Only returns one fun-point 0*(50/10) + 10 = 1'     => [
                1, HafasTravelType::NATIONAL_EXPRESS, now()->addMinutes(62), now()->addMinutes(100), TripSource::HAFAS
            ],
            'just before the Arrival 50/10 + 10 = 15'                          => [
                15, HafasTravelType::NATIONAL_EXPRESS, now()->subMinutes(62), now()->addMinute(), TripSource::HAFAS
            ],
            'upto 60min after the Arrival (50/10 + 10) * 0.25 = 4'             => [
                4, HafasTravelType::NATIONAL_EXPRESS, now()->subMinutes(92), now()->subMinutes(35), TripSource::HAFAS
            ],
            'longer in the past Only returns one fun-point 0*(50/10) + 10 = 1' => [
                1, HafasTravelType::NATIONAL_EXPRESS, now()->subMinutes(62), now()->subMinutes(61), TripSource::HAFAS
            ],
            'manual trip'                                                      => [
                0, HafasTravelType::NATIONAL_EXPRESS, now()->subMinutes(62), now()->subMinutes(61), TripSource::USER
            ],
        ];
    }

    /**
     * @dataProvider calculatePointsDataProvider
     */
    public function testCalculateTrainPoints(int $expectedPoints, HafasTravelType $hafasTravelType, Carbon $departure, Carbon $arrival, TripSource $tripSource): void {
        $this->assertEquals($expectedPoints, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            hafasTravelType: $hafasTravelType,
            departure:       $departure,
            arrival:         $arrival,
            tripSource:      $tripSource,
        )->points);
    }
}
