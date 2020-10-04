<?php

namespace Tests\Unit;

use App\Http\Controllers\TransportController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransportControllerTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Just the good things.
     */
    public function testCalculateTrainPoints_positive_tests() {
        // 50km in an IC/ICE => 50/10 + 10 = 15 points
        $this->assertEquals(15, TransportController::CalculateTrainPoints(50, 'nationalExpress', "-2 minutes", "+10 minutes", 0));
        // 50km in an RB => 50/10 + 5 = 10 points
        $this->assertEquals(10, TransportController::CalculateTrainPoints(50, 'regional', "-2 minutes", "+10 minutes", 0));
        // 18km in a Bus => 20/10 + 2 = 4 points
        $this->assertEquals(4, TransportController::CalculateTrainPoints(18, 'bus', "-2 minutes", "+10 minutes", 0));
    }

    /**
     * If the connection was not on time, we should still get the same points.
     * 50km in an IC/ICE => 15 points.
     * Everything is 30min late, assuming that departure delay = arrival delay (That's just how traewelling works).
     */
    public function testCalculateTrainPoints_delayed_trains() {
        $this->assertEquals(15, TransportController::CalculateTrainPoints(50, 'nationalExpress', "-32 minutes", "-20 minutes", 30 * 60));
    }

    /**
     * Unknown transport product, gives 1 point blanco.
     * 50km in an unknown mode of transport => 50/10 + 1 = 6 points
     */
    public function testCalculateTrainPoints_unknown_product() {
        $now = time();
        $this->assertEquals(6, TransportController::CalculateTrainPoints(50, 'unknown_mode_of_transport', "-2 minutes", "+10 minutes", 0));
    }

    /**
     * I'm trying to check-into trains that depart in the future.
     */
    public function testCalculateTrainPoints_early_checkins() {
        $now = time();

        // < 20min before
        // 50/10 + 10 = 15
        $this->assertEquals(15, TransportController::CalculateTrainPoints(50, 'nationalExpress', $now + 18 * 60 /* departure 18min from now */, $now + 40 * 60, 0));

        // < 60min before, but > 20min
        // (50/10 + 10) * 0.25 = 4
        $this->assertEquals(4, TransportController::CalculateTrainPoints(50, 'nationalExpress', $now + 40 * 60 /* departure 40min from now */, $now + 100 * 60, 0));

        // > 60min before
        // Only returns one fun-point
        // 0*(50/10) + 10 = 1
        $this->assertEquals(1, TransportController::CalculateTrainPoints(50, 'nationalExpress', $now + 62 * 60 /* departure 62min from now */, $now + 100 * 60, 0));
    }

    /**
     * I'm trying to check-into trains that have depart in the past.
     */
    public function testCalculateTrainPoints_late_checkins() {
        $now = time();

        // just before the Arrival
        // 50/10 + 10 = 15
        $this->assertEquals(15, TransportController::CalculateTrainPoints(50, 'nationalExpress', $now - 62 * 60, $now + 1 * 60, 0));

        // upto 60min after the Arrival
        // (50/10 + 10) * 0.25 = 4
        $this->assertEquals(4, TransportController::CalculateTrainPoints(50, 'nationalExpress', $now - 92 * 60, $now - 35 * 60, 0));

        // longer in the past
        // Only returns one fun-point
        // 0*(50/10) + 10 = 1
        $this->assertEquals(1, TransportController::CalculateTrainPoints(50, 'nationalExpress', $now - 62 * 60, $now - 61 * 60, 0));
    }
}
