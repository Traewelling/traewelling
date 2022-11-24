<?php

namespace App\Http\Controllers\Backend\Stats;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

abstract class YearInReviewController extends Controller
{

    public static function get(User $user, int $year): array {
        return Cache::remember(self::cacheKey($user, $year), Carbon::now()->addWeek(), static function() use ($user, $year) {
            return self::generate($user, $year);
        });
    }

    public static function renew(User $user, int $year): array {
        Cache::forget(self::cacheKey($user, $year));
        return self::get($user, $year);
    }

    private static function cacheKey(User $user, int $year): string {
        return "year-in-review-{$user->id}-{$year}";
    }

    public static function generate(User $user, int $year): array {
        $from                       = Carbon::create($year, 1, 1);
        $to                         = Carbon::create($year, 12, 31);
        $sum                        = TransportStatsController::sum($user, $from, $to);
        $sumByHafasByDistance       = TransportStatsController::sumByHafasOperator($user, $from, $to, 'distance', 5);
        $sumByHafasByDuration       = TransportStatsController::sumByHafasOperator($user, $from, $to, 'duration', 5);
        $topOperatorLinesByDistance = TransportStatsController::sumByHafasOperatorAndLine($user, $from, $to, 'distance', 5);
        $topOperatorLinesByDuration = TransportStatsController::sumByHafasOperatorAndLine($user, $from, $to, 'duration', 5);
        $longestTripsByDistance     = TransportStatsController::getLongestTrips($user, $from, $to, 'distance', 5);
        $longestTripsByDuration     = TransportStatsController::getLongestTrips($user, $from, $to, 'duration', 5);
        $fastestTrips               = TransportStatsController::getTripsBySpeed($user, $from, $to, 'desc', 5);
        $slowestTrips               = TransportStatsController::getTripsBySpeed($user, $from, $to, 'asc', 5);
        $mostDelayedArrivals        = TransportStatsController::getTripsByArrivalDelay($user, $from, $to, 'desc', 5);

        return [
            'user'                => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
            ],
            'year'                => $year,
            'distance'            => [
                'total'         => $sum->distance,
                'averagePerDay' => $sum->distance / $from->diffInDays($to),
            ],
            'duration'            => [
                'total'         => $sum->duration,
                'averagePerDay' => $sum->duration / $from->diffInDays($to),
            ],
            'operators'           => [
                'distance' => $sumByHafasByDistance->map(static function($row) {
                    return [
                        'operator' => $row->name,
                        'distance' => $row->distance,
                    ];
                })->toArray(),
                'duration' => $sumByHafasByDuration->map(static function($row) {
                    return [
                        'operator' => $row->name,
                        'duration' => $row->duration,
                    ];
                })->toArray(),
            ],
            'lines'               => [
                'distance' => $topOperatorLinesByDistance->map(static function($row) {
                    return [
                        'operator' => $row->name,
                        'line'     => $row->linename,
                        'distance' => $row->distance,
                    ];
                })->toArray(),
                'duration' => $topOperatorLinesByDuration->map(static function($row) {
                    return [
                        'operator' => $row->name,
                        'line'     => $row->linename,
                        'duration' => $row->duration,
                    ];
                })->toArray(),
            ],
            'longestTrips'        => [
                'distance' => $longestTripsByDistance->map(static function($row) {
                    return [
                        //TODO: add more infos about the trip
                        'distance' => $row->distance,
                    ];
                })->toArray(),
                'duration' => $longestTripsByDuration->map(static function($row) {
                    return [
                        //TODO: add more infos about the trip
                        'duration' => $row->duration,
                    ];
                })->toArray(),
            ],
            'fastestTrips'        => $fastestTrips->map(static function($row) {
                return [
                    //TODO: add more infos about the trip
                    'duration' => $row->duration,
                    'speed'    => $row->speed,
                ];
            })->toArray(),
            'slowestTrips'        => $slowestTrips->map(static function($row) {
                return [
                    //TODO: add more infos about the trip
                    'duration' => $row->duration,
                    'speed'    => $row->speed,
                ];
            })->toArray(),
            'mostDelayedArrivals' => $mostDelayedArrivals->map(static function($row) {
                return [
                    //TODO: add more infos about the trip
                    'delay' => $row->delay,
                ];
            })->toArray(),

        ];
    }
}
