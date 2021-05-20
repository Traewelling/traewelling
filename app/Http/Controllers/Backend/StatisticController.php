<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use stdClass;

class StatisticController extends Controller
{
    public static function getGlobalCheckInStats(Carbon $since, Carbon $until): stdClass {
        if ($since->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->where('train_checkins.departure', '>=', $since->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->select([
                              DB::raw('SUM(train_checkins.distance) AS distance'),
                              DB::raw(
                                  'SUM(TIMESTAMPDIFF(SECOND, train_checkins.departure, train_checkins.arrival)) AS duration'
                              ),
                              DB::raw('COUNT(DISTINCT statuses.user_id) AS user_count')
                          ])
                 ->first();
    }

    public static function getTopTravelCategoryByUser(
        User $user,
        Carbon $since,
        Carbon $until,
        int $limit = 10
    ): Collection {
        if ($since->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $since->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy('hafas_trips.category')
                 ->select(['hafas_trips.category', DB::raw('COUNT(*) AS count')])
                 ->limit($limit)
                 ->get()
                 ->map(function($data) {
                     $data->category = __('transport_types.' . $data->category);
                     return $data;
                 })
                 ->groupBy('category')
                 ->map(function($data) {
                     return $data->sum('count');
                 })
                 ->sort();
    }

    public static function getTopTripOperatorByUser(
        User $user,
        Carbon $since,
        Carbon $until,
        int $limit = 10
    ): Collection {
        if ($since->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                 ->join('hafas_operators', 'hafas_operators.id', '=', 'hafas_trips.operator_id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $since->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy('hafas_operators.name')
                 ->select(['hafas_operators.name', DB::raw('COUNT(*) AS count')])
                 ->orderByDesc(DB::raw('COUNT(*)'))
                 ->limit($limit)
                 ->get();
    }

    public static function getWeeklyTravelTimeByUser(User $user, Carbon $since, Carbon $until): Collection {
        if ($since->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $since->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy([DB::raw('YEAR(train_checkins.departure)'), DB::raw('WEEK(train_checkins.departure, 1)')])
                 ->select([
                              DB::raw('YEAR(train_checkins.departure) AS year'),
                              DB::raw('WEEK(train_checkins.departure, 1) AS kw'),
                              DB::raw('SUM(TIMESTAMPDIFF(MINUTE, departure, arrival)) AS duration')
                          ])
                 ->orderBy(DB::raw('YEAR(train_checkins.departure)'))
                 ->orderBy(DB::raw('WEEK(train_checkins.departure, 1)'))
                 ->get()
                 ->map(function($row) {
                     $row->date = Carbon::today()->setISODate($row->year, $row->kw);
                     return $row;
                 });
    }
}
