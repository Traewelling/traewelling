<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Dto\PointCalculation;
use App\Enum\HafasTravelType;
use App\Enum\PointReason;
use App\Enum\TripSource;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;

abstract class PointsCalculationController extends Controller
{

    private const REDUCED_POINTS = [
        PointReason::NOT_SUFFICIENT,
        PointReason::FORCED,
        PointReason::MANUAL_TRIP,
    ];

    public static function calculatePoints(
        int             $distanceInMeter,
        HafasTravelType $hafasTravelType,
        Carbon          $departure,
        Carbon          $arrival,
        TripSource      $tripSource,
        bool            $forceCheckin = false,
        Carbon          $timestampOfView = null
    ): PointCalculation {
        if (auth()->user()?->points_enabled === false) {
            return self::returnZeroPoints();
        }

        if ($timestampOfView == null) {
            $timestampOfView = now();
        }

        $base     = config('trwl.base_points.train.' . $hafasTravelType->value, 1);
        $distance = ceil($distanceInMeter / 10000);

        return self::calculatePointsWithReason(
            basePoints:     $base,
            distancePoints: $distance,
            pointReason:    self::getReason($departure, $arrival, $forceCheckin, $tripSource, $timestampOfView),
        );
    }

    #[Pure]
    private static function calculatePointsWithReason(
        float       $basePoints,
        float       $distancePoints,
        PointReason $pointReason
    ): PointCalculation {
        $factor = self::getFactorByReason($pointReason);

        return new PointCalculation(
            points:         self::getPointsByReason($pointReason, ($basePoints + $distancePoints), $factor),
            basePoints:     $basePoints,
            distancePoints: $distancePoints,
            reason:         $pointReason,
            factor:         $factor,
        );
    }

    private static function returnZeroPoints(): PointCalculation {
        return new PointCalculation(
            points:         0,
            basePoints:     0,
            distancePoints: 0,
            reason:         PointReason::POINTS_DISABLED,
            factor:         0,
        );
    }

    public static function getPointsByReason(PointReason $pointReason, int $points, float $factor): int {
        if (in_array($pointReason, self::REDUCED_POINTS)) {
            return $pointReason === PointReason::MANUAL_TRIP ? 0 : 1;
        }

        return ceil($points * $factor);
    }

    #[Pure]
    public static function getFactorByReason(PointReason $pointReason): float|int {
        if (in_array($pointReason, self::REDUCED_POINTS)) {
            return 0;
        }
        if ($pointReason === PointReason::GOOD_ENOUGH) {
            return 0.25;
        }
        return 1;
    }

    public static function getReason(
        Carbon     $departure,
        Carbon     $arrival,
        bool       $forceCheckin,
        TripSource $tripSource,
        Carbon     $timestampOfView
    ): PointReason {
        if ($tripSource === TripSource::USER) {
            return PointReason::MANUAL_TRIP;
        }
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
        if ($timestampOfView->isBetween(
            $departure->clone()->subMinutes(config('trwl.base_points.time_window.in_time.before')),
            $arrival->clone()->addMinutes(config('trwl.base_points.time_window.in_time.after'))
        )) {
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
        if ($timestampOfView->isBetween(
            $departure->clone()->subMinutes(config('trwl.base_points.time_window.good_enough.before')),
            $arrival->clone()->addMinutes(config('trwl.base_points.time_window.good_enough.after'))
        )) {
            return PointReason::GOOD_ENOUGH;
        }

        // Else: Just give me one. It's a point for funsies and the minimal amount of points that you can get.
        return PointReason::NOT_SUFFICIENT;
    }
}
