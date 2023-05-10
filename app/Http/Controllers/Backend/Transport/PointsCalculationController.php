<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Dto\PointCalculation;
use App\Enum\HafasTravelType;
use App\Enum\PointReason;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;

abstract class PointsCalculationController extends Controller
{

    public static function calculatePoints(
        int             $distanceInMeter,
        HafasTravelType $hafasTravelType,
        Carbon          $departure,
        Carbon          $arrival,
        bool            $forceCheckin = false,
        Carbon          $timestampOfView = null
    ): PointCalculation {
        if ($timestampOfView == null) {
            $timestampOfView = Carbon::now();
        }

        $base     = config('trwl.base_points.train.' . $hafasTravelType->value, 1);
        $distance = ceil($distanceInMeter / 10000);

        return self::calculatePointsWithReason(
            basePoints:       $base,
            distancePoints:   $distance,
            pointReason:      self::getReason($departure, $arrival, $forceCheckin, $timestampOfView),
        );
    }

    #[Pure]
    private static function calculatePointsWithReason(
        float       $basePoints,
        float       $distancePoints,
        PointReason $pointReason
    ): PointCalculation {
        if ($pointReason === PointReason::NOT_SUFFICIENT || $pointReason === PointReason::FORCED) {
            return new PointCalculation(
                points:           1,
                basePoints:       $basePoints,
                distancePoints:   $distancePoints,
                reason:           $pointReason,
                factor:           0,
            );
        }
        $factor = self::getFactorByReason($pointReason);

        $basePoints     *= $factor;
        $distancePoints *= $factor;

        return new PointCalculation(
            points:           ceil($basePoints + $distancePoints),
            basePoints:       $basePoints,
            distancePoints:   $distancePoints,
            reason:           $pointReason,
            factor:           $factor,
        );
    }

    #[Pure]
    public static function getFactorByReason(PointReason $pointReason): float|int {
        if ($pointReason === PointReason::NOT_SUFFICIENT || $pointReason === PointReason::FORCED) {
            return 0;
        }
        if ($pointReason === PointReason::GOOD_ENOUGH) {
            return 0.25;
        }
        return 1;
    }

    #[Pure]
    public static function getReason(
        Carbon $departure,
        Carbon $arrival,
        bool   $forceCheckin,
        Carbon $timestampOfView
    ): PointReason {
        if ($forceCheckin) {
            return PointReason::FORCED;
        }

        /**
         * Full points, 20min before the departure time or during the ride
         *   D-20         D                      A
         *    |           |                      |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        if ($timestampOfView->isBetween($departure->clone()->subMinutes(20), $arrival)) {
            return PointReason::IN_TIME;
        }

        /**
         * Reduced points, one hour before departure and one hour after arrival
         *
         *   D-60         D          A          A+60
         *    |           |          |           |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        if ($timestampOfView->isBetween($departure->clone()->subHour(), $arrival->clone()->addHour())) {
            return PointReason::GOOD_ENOUGH;
        }

        // Else: Just give me one. It's a point for funsies and the minimal amount of points that you can get.
        return PointReason::NOT_SUFFICIENT;
    }
}
