<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

abstract class PointsCalculationController extends Controller
{

    public static function getBasePoints(int $distanceInMeter, string $category): int {
        $factor = config('trwl.base_points.train.' . $category, 1);
        return $factor + ceil($distanceInMeter / 10000);
    }

    public static function getReducedPoints(int $distanceInMeter, string $category): int {
        return ceil(self::getBasePoints($distanceInMeter, $category) * 0.25);
    }

    public static function calculatePoints(
        int    $distanceInMeter,
        string $category,
        Carbon $departure,
        Carbon $arrival,
        Carbon $timestampOfView = null
    ): int {
        if ($timestampOfView == null) {
            $timestampOfView = Carbon::now();
        }

        /**
         * Full points, 20min before the departure time or during the ride
         *   D-20         D                      A
         *    |           |                      |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        if ($timestampOfView->isBetween($departure->clone()->subMinutes(20), $arrival)) {
            return self::getBasePoints($distanceInMeter, $category);
        }

        /**
         * Reduced points, one hour before departure and after arrival
         *
         *   D-60         D          A          A+60
         *    |           |          |           |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        if ($timestampOfView->isBetween($departure->clone()->subHour(), $arrival->clone()->addHour())) {
            return self::getReducedPoints($distanceInMeter, $category);
        }

        // Else: Just give me one. It's a point for funsies and the minimal amount of points that you can get.
        return 1;
    }

}
