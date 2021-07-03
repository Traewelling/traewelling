<?php

namespace Tests\Unit;

use App\Enum\HafasTravelType;
use App\Http\Controllers\TransportController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransportControllerTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Just the good things.
     */
    public function testCalculateTrainPoints_positive_tests() {
        // 50km in an IC/ICE => 50/10 + 10 = 15 points
        $this->assertEquals(15, TransportController::CalculateTrainPoints(
            distance: 50,
            category: HafasTravelType::NATIONAL_EXPRESS,
            departure: "-2 minutes",
            arrival: "+10 minutes",
            delay: 0
        ));
        // 50km in an RB => 50/10 + 5 = 10 points
        $this->assertEquals(10, TransportController::CalculateTrainPoints(
            distance: 50,
            category: HafasTravelType::REGIONAL,
            departure: "-2 minutes",
            arrival: "+10 minutes",
            delay: 0
        ));
        // 18km in a Bus => 20/10 + 2 = 4 points
        $this->assertEquals(4, TransportController::CalculateTrainPoints(
            distance: 18,
            category: HafasTravelType::BUS,
            departure: "-2 minutes",
            arrival: "+10 minutes",
            delay: 0
        ));
    }

    /**
     * If the connection was not on time, we should still get the same points.
     * 50km in an IC/ICE => 15 points.
     * Everything is 30min late, assuming that departure delay = arrival delay (That's just how traewelling works).
     */
    public function testCalculateTrainPoints_delayed_trains() {
        $this->assertEquals(15, TransportController::CalculateTrainPoints(
            distance: 50,
            category: HafasTravelType::NATIONAL_EXPRESS,
            departure: "-32 minutes",
            arrival: "-20 minutes",
            delay: 30 * 60
        ));
    }

    /**
     * Unknown transport product, gives 1 point blanco.
     * 50km in an unknown mode of transport => 50/10 + 1 = 6 points
     */
    public function testCalculateTrainPoints_unknown_product() {
        $this->assertEquals(6, TransportController::CalculateTrainPoints(
            distance: 50,
            category: 'unknown_mode_of_transport',
            departure: "-2 minutes",
            arrival: "+10 minutes",
            delay: 0
        ));
    }

    /**
     * I'm trying to check-into trains that depart in the future.
     */
    public function testCalculateTrainPoints_early_checkins() {
        $now = time();

        // < 20min before
        // 50/10 + 10 = 15
        $this->assertEquals(15, TransportController::CalculateTrainPoints(
            distance: 50,
            category: HafasTravelType::NATIONAL_EXPRESS,
            departure: $now + 18 * 60 /* departure 18min from now */,
            arrival: $now + 40 * 60,
            delay: 0
        ));

        // < 60min before, but > 20min
        // (50/10 + 10) * 0.25 = 4
        $this->assertEquals(4, TransportController::CalculateTrainPoints(
            distance: 50,
            category: HafasTravelType::NATIONAL_EXPRESS,
            departure: $now + 40 * 60 /* departure 40min from now */,
            arrival: $now + 100 * 60,
            delay: 0
        ));

        // > 60min before
        // Only returns one fun-point
        // 0*(50/10) + 10 = 1
        $this->assertEquals(1, TransportController::CalculateTrainPoints(
            distance: 50,
            category: HafasTravelType::NATIONAL_EXPRESS,
            departure: $now + 62 * 60 /* departure 62min from now */,
            arrival: $now + 100 * 60,
            delay: 0
        ));
    }

    /**
     * I'm trying to check-into trains that have depart in the past.
     */
    public function testCalculateTrainPoints_late_checkins() {
        $now = time();

        // just before the Arrival
        // 50/10 + 10 = 15
        $this->assertEquals(15, TransportController::CalculateTrainPoints(
            distance: 50,
            category: HafasTravelType::NATIONAL_EXPRESS,
            departure: $now - 62 * 60,
            arrival: $now + 1 * 60,
            delay: 0
        ));

        // upto 60min after the Arrival
        // (50/10 + 10) * 0.25 = 4
        $this->assertEquals(4, TransportController::CalculateTrainPoints(
            distance: 50,
            category: HafasTravelType::NATIONAL_EXPRESS,
            departure: $now - 92 * 60,
            arrival: $now - 35 * 60,
            delay: 0
        ));

        // longer in the past
        // Only returns one fun-point
        // 0*(50/10) + 10 = 1
        $this->assertEquals(1, TransportController::CalculateTrainPoints(
            distance: 50,
            category: HafasTravelType::NATIONAL_EXPRESS,
            departure: $now - 62 * 60,
            arrival: $now - 61 * 60,
            delay: 0
        ));
    }
}
