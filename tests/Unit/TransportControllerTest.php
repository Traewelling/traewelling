<?php

namespace Tests\Unit;

use App\Enum\HafasTravelType;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use Carbon\Carbon;

class TransportControllerTest extends UnitTestCase
{

    public static function calculatePointsDataProvider() {
        return [
            '50km in an IC/ICE => 50/10 + 10 = 15 points' => [
                15, HafasTravelType::NATIONAL_EXPRESS, Carbon::now()->subMinutes(2), Carbon::now()->addMinutes(10)
            ],
            '50km in an RB => 50/10 + 5 = 10 points'      => [
                10, HafasTravelType::REGIONAL, Carbon::now()->subMinutes(2), Carbon::now()->addMinutes(10)
            ],
            '18km in a Bus => 20/10 + 2 = 4 points'       => [
                7, HafasTravelType::BUS, Carbon::now()->subMinutes(2), Carbon::now()->addMinutes(10)
            ],
            '< 20min before 50/10 + 10 = 15'                               => [
                15, HafasTravelType::NATIONAL_EXPRESS, Carbon::now()->addMinutes(18), Carbon::now()->addMinutes(40)
            ],
            '< 60min before, but > 20min (50/10 + 10) * 0.25 = 4'          => [
                4, HafasTravelType::NATIONAL_EXPRESS, Carbon::now()->addMinutes(40), Carbon::now()->addMinutes(100)
            ],
            '> 60min before Only returns one fun-point 0*(50/10) + 10 = 1' => [
                1, HafasTravelType::NATIONAL_EXPRESS, Carbon::now()->addMinutes(62), Carbon::now()->addMinutes(100)
            ],
            'just before the Arrival 50/10 + 10 = 15' => [
                15, HafasTravelType::NATIONAL_EXPRESS, Carbon::now()->subMinutes(62), Carbon::now()->addMinute()
            ],
            'upto 60min after the Arrival (50/10 + 10) * 0.25 = 4' => [
                4, HafasTravelType::NATIONAL_EXPRESS, Carbon::now()->subMinutes(92), Carbon::now()->subMinutes(35)
            ],
            'longer in the past Only returns one fun-point 0*(50/10) + 10 = 1' => [
                1, HafasTravelType::NATIONAL_EXPRESS, Carbon::now()->subMinutes(62), Carbon::now()->subMinutes(61)
            ],

        ];
    }

    /**
     * @dataProvider calculatePointsDataProvider
     */
    public function testCalculateTrainPoints(int $expectedPoints, HafasTravelType $hafasTravelType, Carbon $departure, Carbon $arrival): void {
        $this->assertEquals($expectedPoints, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            hafasTravelType: $hafasTravelType,
            departure:       $departure,
            arrival:         $arrival,
        )->points);
    }
}
