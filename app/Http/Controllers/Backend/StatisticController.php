<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TrainStation;
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
                 ->get();
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
                $obj->count    = $row->count;
                $obj->duration = $row->duration;
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
                 ->get();
    }

    public static function getUsedStations(User $user, Carbon $from, Carbon $until): Collection {
        $qUsedStations = DB::table('train_checkins')
                           ->where('user_id', '=', $user->id)
                           ->where('departure', '>=', $from->toIso8601String())
                           ->where('departure', '<=', $until->toIso8601String())
                           ->select(['origin', 'destination'])
                           ->get();

        $usedStationIds = $qUsedStations->pluck('origin')
                                        ->merge($qUsedStations->pluck('destination'))
                                        ->unique();

        return TrainStation::whereIn('ibnr', $usedStationIds)->get();
    }

    public static function getPassedStations(User $user, Carbon $from, Carbon $to): Collection {
        $checkIns = DB::table('train_checkins')
                      ->where('user_id', '=', $user->id)
                      ->where('departure', '>=', $from->toIso8601String())
                      ->where('departure', '<=', $to->toIso8601String())
                      ->select(['trip_id', 'departure', 'arrival'])
                      ->get();

        $stopoverQ = DB::table('train_stopovers')->select('train_station_id');
        foreach ($checkIns as $checkIn) {
            $stopoverQ->orWhere(function($q) use ($checkIn) {
                $q->where('trip_id', '=', $checkIn->trip_id)
                  ->where('departure_planned', '>', $checkIn->departure)
                  ->where('departure_planned', '<', $checkIn->arrival);
            });
            $stopoverQ->orWhere(function($q) use ($checkIn) {
                $q->where('trip_id', '=', $checkIn->trip_id)
                  ->where('arrival_planned', '>', $checkIn->departure)
                  ->where('arrival_planned', '<', $checkIn->arrival);
            });
        }

        $passedStationIds = $stopoverQ->pluck('train_station_id')
                                      ->unique();

        return TrainStation::whereIn('id', $passedStationIds)->get();
    }
}
