<?php

namespace Tests\Unit;

use App\Enum\HafasTravelType;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use App\Http\Controllers\TransportController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class TransportControllerTest extends TestCase {

    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Just the good things.
     */
    public function testCalculateTrainPoints_positive_tests() {
        // 50km in an IC/ICE => 50/10 + 10 = 15 points
        $this->assertEquals(15, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            category:        HafasTravelType::NATIONAL_EXPRESS,
            departure:       Carbon::now()->subMinutes(2),
            arrival:         Carbon::now()->addMinutes(10),
        ));
        // 50km in an RB => 50/10 + 5 = 10 points
        $this->assertEquals(10, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            category:        HafasTravelType::REGIONAL,
            departure:       Carbon::now()->subMinutes(2),
            arrival:         Carbon::now()->addMinutes(10),
        ));
        // 18km in a Bus => 20/10 + 2 = 4 points
        $this->assertEquals(4, PointsCalculationController::calculatePoints(
            distanceInMeter: 18000,
            category:        HafasTravelType::BUS,
            departure:       Carbon::now()->subMinutes(2),
            arrival:         Carbon::now()->addMinutes(10),
        ));
    }

    /**
     * Unknown transport product, gives 1 point blanco.
     * 50km in an unknown mode of transport => 50/10 + 1 = 6 points
     */
    public function testCalculateTrainPoints_unknown_product() {
        $this->assertEquals(6, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            category:        'unknown_mode_of_transport',
            departure:       Carbon::now()->subMinutes(2),
            arrival:         Carbon::now()->addMinutes(10),
        ));
    }

    /**
     * I'm trying to check-into trains that depart in the future.
     */
    public function testCalculateTrainPoints_early_checkins() {
        // < 20min before
        // 50/10 + 10 = 15
        $this->assertEquals(15, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            category:        HafasTravelType::NATIONAL_EXPRESS,
            departure:       Carbon::now()->addMinutes(18),
            arrival:         Carbon::now()->addMinutes(40),
        ));

        // < 60min before, but > 20min
        // (50/10 + 10) * 0.25 = 4
        $this->assertEquals(4, PointsCalculationController::calculatePointss(
            distanceInMeter: 50000,
            category:        HafasTravelType::NATIONAL_EXPRESS,
            departure:       Carbon::now()->addMinutes(40),
            arrival:         Carbon::now()->addMinutes(100),
        ));

        // > 60min before
        // Only returns one fun-point
        // 0*(50/10) + 10 = 1
        $this->assertEquals(1, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            category:        HafasTravelType::NATIONAL_EXPRESS,
            departure:       Carbon::now()->addMinutes(62),
            arrival:         Carbon::now()->addMinutes(100),
        ));
    }

    /**
     * I'm trying to check-into trains that have depart in the past.
     */
    public function testCalculateTrainPoints_late_checkins() {
        // just before the Arrival
        // 50/10 + 10 = 15
        $this->assertEquals(15, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            category:        HafasTravelType::NATIONAL_EXPRESS,
            departure:       Carbon::now()->subMinutes(62),
            arrival:         Carbon::now()->addMinute(),
        ));

        // upto 60min after the Arrival
        // (50/10 + 10) * 0.25 = 4
        $this->assertEquals(4, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            category:        HafasTravelType::NATIONAL_EXPRESS,
            departure:       Carbon::now()->subMinutes(92),
            arrival:         Carbon::now()->subMinutes(35),
        ));

        // longer in the past
        // Only returns one fun-point
        // 0*(50/10) + 10 = 1
        $this->assertEquals(1, PointsCalculationController::calculatePoints(
            distanceInMeter: 50000,
            category:        HafasTravelType::NATIONAL_EXPRESS,
            departure:       Carbon::now()->subMinutes(62),
            arrival:         Carbon::now()->subMinutes(61),
        ));
    }
}
