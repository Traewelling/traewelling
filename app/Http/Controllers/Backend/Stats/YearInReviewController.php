<?php

namespace App\Http\Controllers\Backend\Stats;

use App\Helpers\CacheKey;
use App\Http\Controllers\Controller;
use App\Http\Resources\StationResource;
use App\Http\Resources\StatusResource;
use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

abstract class YearInReviewController extends Controller
{

    public static function get(User $user, int $year): array {
        return Cache::remember(CacheKey::getYearInReviewKey($user, $year), Carbon::now()->addWeek(), static function() use ($user, $year) {
            return self::generate($user, $year);
        });
    }

    public static function renew(User $user, int $year): array {
        Cache::forget(CacheKey::getYearInReviewKey($user, $year));
        return self::get($user, $year);
    }

    public static function generate(User $user, int $year): array {
        $from                       = Carbon::create($year, 1, 1);
        $to                         = Carbon::create($year, 12, 31);
        $count                      = TransportStatsController::count($user, $from, $to);
        $sum                        = TransportStatsController::sum($user, $from, $to);
        $countHafasOperators        = TransportStatsController::countHafasOperators($user, $from, $to);
        $sumByHafasByDistance       = TransportStatsController::sumByHafasOperator($user, $from, $to, 'distance', 1);
        $sumByHafasByDuration       = TransportStatsController::sumByHafasOperator($user, $from, $to, 'duration', 1);
        $topOperatorLinesByDistance = TransportStatsController::sumByHafasOperatorAndLine($user, $from, $to, 'distance', 1);
        $topOperatorLinesByDuration = TransportStatsController::sumByHafasOperatorAndLine($user, $from, $to, 'duration', 1);
        $longestTripsByDistance     = TransportStatsController::getLongestTrips($user, $from, $to, 'distance', 1);
        $longestTripsByDuration     = TransportStatsController::getLongestTrips($user, $from, $to, 'duration', 1);
        $fastestTrips               = TransportStatsController::getTripsBySpeed($user, $from, $to, 'desc', 1);
        $slowestTrips               = TransportStatsController::getTripsBySpeed($user, $from, $to, 'asc', 1);
        $mostDelayedArrivals        = TransportStatsController::getTripsByArrivalDelay($user, $from, $to, 'desc', 1);
        $topDestinations            = TransportStatsController::getTopDestinations($user, $from, $to, 5);
        $lonelyStations             = TransportStatsController::getLonelyStations($user, $from, $to);
        $mostLikedStatuses          = TransportStatsController::getMostLikedStatus($user, $from, $to);

        return [
            'year'                => $year,
            'user'                => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
            ],
            'count'               => $count,
            'distance'            => [
                'total'         => round($sum->distance),
                'averagePerDay' => round($sum->distance / $from->diffInDays($to), 3),
            ],
            'duration'            => [
                'total'         => round($sum->duration),
                'averagePerDay' => round($sum->duration / $from->diffInDays($to), 3),
            ],
            'totalDelay'          => TransportStatsController::getTotalArrivalDelay($user, $from, $to),
            'operators'           => [
                'count'         => $countHafasOperators,
                'topByDistance' => $sumByHafasByDistance->map(static function($row) {
                    return [
                        'operator' => $row->name,
                        'distance' => round($row->distance),
                    ];
                })->first(),
                'topByDuration' => $sumByHafasByDuration->map(static function($row) {
                    return [
                        'operator' => $row->name,
                        'duration' => round($row->duration),
                    ];
                })->first(),
            ],
            'lines'               => [
                'topByDistance' => $topOperatorLinesByDistance->map(static function($row) {
                    return [
                        'operator' => $row->name,
                        'line'     => $row->linename,
                        'distance' => round($row->distance),
                    ];
                })->first(),
                'topByDuration' => $topOperatorLinesByDuration->map(static function($row) {
                    return [
                        'operator' => $row->name,
                        'line'     => $row->linename,
                        'duration' => round($row->duration),
                    ];
                })->first(),
            ],
            'longestTrips'        => [
                'distance' => $longestTripsByDistance->map(static function(Checkin $checkin) {
                    return new StatusResource($checkin->status);
                })->first(),
                'duration' => $longestTripsByDuration->map(static function(Checkin $checkin) {
                    return new StatusResource($checkin->status);
                })->first(),
            ],
            'fastestTrips'        => $fastestTrips->map(static function(Checkin $checkin) {
                return new StatusResource($checkin->status);
            })->first(),
            'slowestTrips'        => $slowestTrips->map(static function(Checkin $checkin) {
                return new StatusResource($checkin->status);
            })->first(),
            'mostDelayedArrivals' => $mostDelayedArrivals->map(static function(Checkin $checkin) {
                return new StatusResource($checkin->status);
            })->first(),
            'topDestinations'     => $topDestinations->map(static function($data) {
                return [
                    'station' => new StationResource($data->station),
                    'count'   => $data->count,
                ];
            }),
            'lonelyStations'      => $lonelyStations->map(static function($station) {
                return [
                    'station' => new StationResource($station),
                    'count'   => $station->count,
                ];
            }),
            'mostLikedStatuses'   => $mostLikedStatuses->map(static function($data) {
                return [
                    'likeCount' => $data['likeCount'],
                    'status'    => new StatusResource($data['status']),
                ];
            }),
        ];
    }
}
