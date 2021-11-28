<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\HafasTravelType;
use App\Enum\PointReasons;
use App\Http\Controllers\Controller;
use App\Http\Resources\PointsCalculationResource;
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
        array           $additional = [],
        Carbon          $timestampOfView = null
    ): PointsCalculationResource {
        if ($timestampOfView == null) {
            $timestampOfView = Carbon::now();
        }

        $base     = config('trwl.base_points.train.' . $hafasTravelType->value, 1);
        $distance = ceil($distanceInMeter / 10000);

        return self::calculatePointsWithReason(
            basePoints:       $base,
            distancePoints:   $distance,
            additionalPoints: $additional,
            pointReason:      self::getReason($departure, $arrival, $forceCheckin, $timestampOfView),
        );
    }

    #[Pure]
    private static function calculatePointsWithReason(
        float        $basePoints,
        float        $distancePoints,
        ?array       $additionalPoints,
        PointReasons $pointReason
    ): PointsCalculationResource {
        if ($pointReason === PointReasons::NOT_SUFFICIENT || $pointReason === PointReasons::FORCED) {
            return new PointsCalculationResource([
                                                     'points'      => 1,
                                                     'calculation' => [
                                                         'base'     => $basePoints,
                                                         'distance' => $distancePoints,
                                                         'reason'   => $pointReason->value,
                                                         'factor'   => 0,
                                                     ],
                                                     'additional'  => $additionalPoints,
                                                 ]);
        }
        $factor = self::getFactorByReason($pointReason);

        $basePoints     *= $factor;
        $distancePoints *= $factor;

        $result = $basePoints + $distancePoints;

        foreach ($additionalPoints as $additional) {
            $factorA = 1;
            if ($additional->divisible) {
                $factorA = $factor;
            }
            $result += $additional->points * $factorA;
        }

        return new PointsCalculationResource([
                                                 'points'      => ceil($result),
                                                 'calculation' => [
                                                     'base'     => $basePoints,
                                                     'distance' => $distancePoints,
                                                     'factor'   => $factor,
                                                     'reason'   => $pointReason->value,
                                                 ],
                                                 'additional'  => $additionalPoints,
                                             ]);
    }

    #[Pure]
    public static function getFactorByReason(PointReasons $pointReason): float|int {
        if ($pointReason === PointReasons::NOT_SUFFICIENT || $pointReason === PointReasons::FORCED) {
            return 0;
        }
        if ($pointReason === PointReasons::GOOD_ENOUGH) {
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
    ): PointReasons {
        if ($forceCheckin) {
            return PointReasons::FORCED;
        }

        /**
         * Full points, 20min before the departure time or during the ride
         *   D-20         D                      A
         *    |           |                      |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        if ($timestampOfView->isBetween($departure->clone()->subMinutes(20), $arrival)) {
            return PointReasons::IN_TIME;
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
            return PointReasons::GOOD_ENOUGH;
        }

        // Else: Just give me one. It's a point for funsies and the minimal amount of points that you can get.
        return PointReasons::NOT_SUFFICIENT;
    }
}
