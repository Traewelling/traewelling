<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\PointReasons;
use App\Http\Controllers\Controller;
use App\Http\Resources\PointsCalculationResource;
use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;

abstract class PointsCalculationController extends Controller
{

    public static function calculatePoints(
        int    $distanceInMeter,
        string $category,
        Carbon $departure,
        Carbon $arrival,
        bool   $forceCheckin = false,
        array  $additional = [],
        Carbon $timestampOfView = null
    ): PointsCalculationResource {
        if ($timestampOfView == null) {
            $timestampOfView = Carbon::now();
        }

        // Else: Just give me one. It's a point for funsies and the minimal amount of points that you can get.
        $reason = PointReasons::NOT_SUFFICIENT;


        /**
         * Full points, 20min before the departure time or during the ride
         *   D-20         D                      A
         *    |           |                      |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        if ($timestampOfView->isBetween($departure->clone()->subMinutes(20), $arrival)) {
            $reason = PointReasons::IN_TIME;
        } /**
         * Reduced points, one hour before departure and after arrival
         *
         *   D-60         D          A          A+60
         *    |           |          |           |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        elseif ($timestampOfView->isBetween($departure->clone()->subHour(), $arrival->clone()->addHour())) {
            $reason = PointReasons::GOOD_ENOUGH;
        }

        if ($forceCheckin) {
            $reason = PointReasons::FORCED;
        }

        $base     = config('trwl.base_points.train.' . $category, 1);
        $distance = ceil($distanceInMeter / 10000);

        return self::calculatePointsWithReason(
            base:      $base,
            distance:  $distance,
            additions: $additional,
            reason:    $reason);
    }

    #[Pure] private static function calculatePointsWithReason(
        float     $base,
        float     $distance,
        ?array    $additions,
        float|int $reason
    ): PointsCalculationResource {
        if ($reason === PointReasons::NOT_SUFFICIENT || $reason === PointReasons::FORCED) {
            return new PointsCalculationResource(['points'      => 1,
                                                  'calculation' => ['base'     => $base,
                                                                    'distance' => $distance,
                                                                    'reason'   => $reason,
                                                                    'factor'   => 0],
                                                  'additional'  => $additions]);
        }
        $factor = 1;
        if ($reason === PointReasons::GOOD_ENOUGH) {
            $factor = 0.25;
        }

        $base     *= $factor;
        $distance *= $factor;
        $result    = $base + $distance;

        foreach ($additions as $additional) {
            $factorA = 1;
            if ($additional->divisible) {
                $factorA = $factor;
            }
            $result += $additional->points * $factorA;
        }

        return new PointsCalculationResource(['points'      => ceil($result),
                                              'calculation' => [
                                                  'base'     => $base,
                                                  'distance' => $distance,
                                                  'factor'   => $factor,
                                                  'reason'   => $reason,
                                              ],
                                              'additional'  => $additions]);
    }

}
