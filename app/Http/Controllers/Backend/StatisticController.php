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

    /**
     * @param Carbon $from
     * @param Carbon $until
     * @return stdClass
     * @api v1
     */
    public static function getGlobalCheckInStats(Carbon $from, Carbon $until): stdClass {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->where('train_checkins.departure', '>=', $from->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->select([
                              DB::raw('SUM(train_checkins.distance) AS distance'),
                              DB::raw('SUM(TIMESTAMPDIFF(SECOND, train_checkins.departure,
                              train_checkins.arrival)) AS duration'),
                              DB::raw('COUNT(DISTINCT statuses.user_id) AS user_count')
                          ])
                 ->first();
    }

    /**
     * @param User $user
     * @param Carbon $from
     * @param Carbon $until
     * @param int $limit
     * @return Collection
     * @api v1
     */
    public static function getTopTravelCategoryByUser(
        User $user,
        Carbon $from,
        Carbon $until,
        int $limit = 10
    ): Collection {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $from->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy('hafas_trips.category')
                 ->select([
                              'hafas_trips.category AS name',
                              DB::raw('COUNT(*) AS count'),
                              DB::raw('SUM(TIMESTAMPDIFF(MINUTE, train_checkins.departure,
                              train_checkins.arrival)) AS duration')
                          ])
                 ->orderByDesc(DB::raw('COUNT(*)'))
                 ->limit($limit)
                 ->get();
    }

    /**
     * @param User $user
     * @param Carbon $from
     * @param Carbon $until
     * @param int $limit
     * @return Collection
     * @api v1
     */
    public static function getTopTripOperatorByUser(
        User $user,
        Carbon $from,
        Carbon $until,
        int $limit = 10
    ): Collection {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                 ->join('hafas_operators', 'hafas_operators.id', '=', 'hafas_trips.operator_id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $from->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy('hafas_operators.name')
                 ->select([
                              'hafas_operators.name',
                              DB::raw('COUNT(*) AS count'),
                              DB::raw('SUM(TIMESTAMPDIFF(MINUTE, train_checkins.departure,
                              train_checkins.arrival)) AS duration')
                          ])
                 ->orderByDesc(DB::raw('COUNT(*)'))
                 ->limit($limit)
                 ->get();
    }

    /**
     * @param User $user
     * @param Carbon $from
     * @param Carbon $until
     * @return Collection
     * @deprecated this will be replaced by getDailyTravelTimeByUser
     */
    public static function getWeeklyTravelTimeByUser(User $user, Carbon $from, Carbon $until): Collection {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $from->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy([DB::raw('YEAR(train_checkins.departure)'), DB::raw('WEEK(train_checkins.departure, 1)')])
                 ->select([
                              DB::raw('YEAR(train_checkins.departure) AS year'),
                              DB::raw('WEEK(train_checkins.departure, 1) AS kw'),
                              DB::raw('COUNT(*) AS count'),
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

    /**
     * @param User $user
     * @param Carbon $from
     * @param Carbon $until
     * @return Collection
     * @api v1
     */
    public static function getDailyTravelTimeByUser(User $user, Carbon $from, Carbon $until): Collection {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }


        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $from->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy([DB::raw('date(train_checkins.departure)')])
                 ->select([
                              DB::raw('DATE(train_checkins.departure) AS date'),
                              DB::raw('COUNT(*) AS count'),
                              DB::raw('SUM(TIMESTAMPDIFF(MINUTE, departure, arrival)) AS duration')
                          ])
                 ->orderBy(DB::raw('date'))
                 ->get();
    }


    /**
     * @param User $user
     * @param Carbon $from
     * @param Carbon $until
     * @return Collection
     * @api v1
     */
    public static function getTravelPurposes(User $user, Carbon $from, Carbon $until): Collection {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $from->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy('statuses.business')
                 ->select([
                              DB::raw('statuses.business AS reason'),
                              DB::raw('COUNT(*) AS count'),
                              DB::raw('SUM(TIMESTAMPDIFF(MINUTE, departure, arrival)) AS duration')
                          ])
                 ->orderByDesc('duration')
                 ->get();
    }
}
