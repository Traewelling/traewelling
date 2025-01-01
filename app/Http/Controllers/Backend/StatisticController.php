<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use stdClass;

abstract class StatisticController extends Controller
{

    /**
     * @param Carbon $from
     * @param Carbon $until
     *
     * @return stdClass
     * @api v1
     */
    public static function getGlobalCheckInStats(Carbon $from, Carbon $until): stdClass {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return self::globalCheckinQuery($from, $until);
    }

    public static function getGlobalCheckInStatsAllTime(): stdClass {
        return self::globalCheckinQuery();
    }

    private static function globalCheckinQuery(?Carbon $from = null, ?Carbon $until = null): stdClass {
        $query = DB::table('train_checkins');

        if ($from !== null && $until !== null) {
            $query->where('train_checkins.departure', '>=', $from->toIso8601String())
                  ->where('train_checkins.departure', '<=', $until->toIso8601String());
        }
        $query->selectRaw('SUM(train_checkins.distance) AS distance');
        $query->selectRaw('COUNT(DISTINCT train_checkins.user_id) AS user_count');

        if (DB::getDriverName() === 'sqlite') {
            $query->selectRaw('1337 AS duration');
        } else {
            $query->selectRaw('SUM(TIMESTAMPDIFF(SECOND, train_checkins.departure, train_checkins.arrival)) AS duration');
        }

        return $query->first();
    }

    /**
     * @param User   $user
     * @param Carbon $from
     * @param Carbon $until
     * @param int    $limit
     *
     * @return Collection
     * @api v1
     */
    public static function getTopTravelCategoryByUser(
        User   $user,
        Carbon $from,
        Carbon $until,
        int    $limit = 10
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
                 ->get()
                 ->map(function($row) {
                     $row->count    = (int) $row->count;
                     $row->duration = (int) $row->duration;
                     return $row;
                 });
    }

    /**
     * @param User   $user
     * @param Carbon $from
     * @param Carbon $until
     * @param int    $limit
     *
     * @return Collection
     * @api v1
     */
    public static function getTopTripOperatorByUser(
        User   $user,
        Carbon $from,
        Carbon $until,
        int    $limit = 10
    ): Collection {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                 ->leftJoin('hafas_operators', 'hafas_operators.id', '=', 'hafas_trips.operator_id')
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
                 ->get()
                 ->map(function($row) {
                     $row->count    = (int) $row->count;
                     $row->duration = (int) $row->duration;
                     return $row;
                 });
    }

    /**
     * @param User   $user
     * @param Carbon $from
     * @param Carbon $until
     *
     * @return Collection
     * @api v1
     */
    public static function getDailyTravelTimeByUser(User $user, Carbon $from, Carbon $until): Collection {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }
        $from->setTime(0, 0);
        $until->setTime(0, 0);

        $dateList = collect();
        for ($date = $from->clone(); $date->isBefore($until); $date->addDay()) {
            $e           = collect();
            $e->date     = $date->clone();
            $e->count    = 0;
            $e->duration = 0;
            $dateList->push($e);
        }

        $data = DB::table('train_checkins')
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

        foreach ($data as $row) {
            $obj = $dateList->where(function($item) use ($row) {
                return $item->date->isSameDay(Carbon::parse($row->date));
            })->first();
            if ($obj) {
                $obj->count    = (int) $row->count;
                $obj->duration = (int) $row->duration;
            } else {
                $e           = collect();
                $e->date     = Carbon::parse($row->date);
                $e->count    = 0;
                $e->duration = 0;
                $dateList->push($e);
            }
        }

        return $dateList->sortBy('date');
    }


    /**
     * @param User   $user
     * @param Carbon $from
     * @param Carbon $until
     *
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
                 ->get()
                 ->map(function($row) {
                     $row->count    = (int) $row->count;
                     $row->duration = (int) $row->duration;
                     return $row;
                 });
    }

    public static function getUsedStations(User $user, Carbon $from, Carbon $until): Collection {
        $qUsedStations = DB::table('train_checkins')
                           ->join('train_stopovers', 'train_checkins.trip_id', '=', 'train_stopovers.trip_id')
                           ->where('user_id', '=', $user->id)
                           ->where('departure', '>=', $from->toIso8601String())
                           ->where('departure', '<=', $until->toIso8601String())
                           ->select(['origin_stopover_id', 'destination_stopover_id'])
                           ->get();

        $usedStationIds = $qUsedStations->pluck('origin_stopover_id')
                                        ->merge($qUsedStations->pluck('destination_stopover_id'))
                                        ->unique();

        return Station::join('train_stopovers', 'train_stopovers.train_station_id', '=', 'train_stations.id')
                      ->whereIn('train_stopovers.id', $usedStationIds)->get();
    }

    public static function getPassedStations(User $user, ?Carbon $from = null, ?Carbon $to = null): Collection {
        $query = DB::table('train_checkins')
                   ->join('train_stopovers', 'train_checkins.trip_id', '=', 'train_stopovers.trip_id')
                   ->where('user_id', '=', $user->id)
                   ->whereRaw('train_checkins.departure <= train_stopovers.departure_planned')
                   ->whereRaw('train_checkins.arrival >= train_stopovers.arrival_planned')
                   ->groupBy('train_stopovers.train_station_id')
                   ->select('train_stopovers.train_station_id');
        if ($from !== null) {
            $query->where('train_checkins.departure', '>=', $from->toIso8601String());
        }
        if ($to !== null) {
            $query->where('train_checkins.departure', '<=', $to->toIso8601String());
        }

        return Station::whereIn('id', $query)->get();
    }
}
