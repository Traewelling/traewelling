<?php

namespace App\Http\Controllers\Backend;

use App\Dto\Internal\GlobalCheckinStats;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

abstract class StatisticController extends Controller
{
    /**
     * @api v1
     */
    public static function getGlobalCheckInStats(Carbon $from, Carbon $until): GlobalCheckinStats
    {
        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return self::globalCheckinQuery($from, $until);
    }

    public static function getGlobalCheckInStatsAllTime(): GlobalCheckinStats
    {
        return self::globalCheckinQuery();
    }

    private static function globalCheckinQuery(?Carbon $from = null, ?Carbon $until = null): GlobalCheckinStats
    {
        $query = DB::table('train_checkins');

        if ($from !== null && $until !== null) {
            $query->where('train_checkins.departure', '>=', $from->toIso8601String())
                ->where('train_checkins.departure', '<=', $until->toIso8601String());
        }
        $query->selectRaw('SUM(train_checkins.distance) AS distance');
        $query->selectRaw('COUNT(DISTINCT train_checkins.user_id) AS userCount');

        if (DB::getDriverName() === 'sqlite') {
            $query->selectRaw('1337 AS duration');
        } else {
            $query->selectRaw('SUM(TIMESTAMPDIFF(SECOND, train_checkins.departure, train_checkins.arrival)) AS duration');
        }

        $result = $query->first();

        return new GlobalCheckinStats(
            $result->distance ?? 0,
            $result->duration ?? 0,
            $result->userCount ?? 0
        );
    }

    /**
     * @api v1
     */
    public static function getTopTravelCategoryByUser(
        User $user,
        Carbon $from,
        Carbon $until,
        int $limit = 10
    ): Collection {
        $from->startOfDay();
        $until->endOfDay();

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
                              train_checkins.arrival)) AS duration'),
            ])
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->count = (int) $row->count;
                $row->duration = (int) $row->duration;

                return $row;
            });
    }

    /**
     * @api v1
     */
    public static function getTopTripOperatorByUser(
        User $user,
        Carbon $from,
        Carbon $until,
        int $limit = 10
    ): Collection {
        $from->startOfDay();
        $until->endOfDay();

        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->leftJoin('operators', 'operators.id', '=', 'hafas_trips.operator_id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.departure', '>=', $from->toIso8601String())
            ->where('train_checkins.departure', '<=', $until->toIso8601String())
            ->groupBy('operators.name')
            ->select([
                'operators.name',
                DB::raw('COUNT(*) AS count'),
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, train_checkins.departure,
                              train_checkins.arrival)) AS duration'),
            ])
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->count = (int) $row->count;
                $row->duration = (int) $row->duration;

                return $row;
            });
    }

    /**
     * @api v1
     */
    public static function getDailyTravelTimeByUser(User $user, Carbon $from, Carbon $until): Collection
    {
        $from->startOfDay();
        $until->endOfDay();

        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        $dateList = collect();
        for ($date = $from->clone(); $date->isBefore($until); $date->addDay()) {
            $collection = collect();
            $collection->date = $date->clone();
            $collection->count = 0;
            $collection->duration = 0;
            $dateList->push($collection);
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
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, departure, arrival)) AS duration'),
            ])
            ->orderBy(DB::raw('date'))
            ->get();

        foreach ($data as $row) {
            $obj = $dateList->where(function ($item) use ($row) {
                return $item->date->isSameDay(Carbon::parse($row->date));
            })->first();
            if ($obj) {
                $obj->count = (int) $row->count;
                $obj->duration = (int) $row->duration;
            } else {
                $collection = collect();
                $collection->date = Carbon::parse($row->date);
                $collection->count = 0;
                $collection->duration = 0;
                $dateList->push($collection);
            }
        }

        return $dateList->sortBy('date');
    }

    /**
     * @api v1
     */
    public static function getTravelPurposes(User $user, Carbon $from, Carbon $until): Collection
    {
        $from->startOfDay();
        $until->endOfDay();

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
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, departure, arrival)) AS duration'),
            ])
            ->orderByDesc('duration')
            ->get()
            ->map(function ($row) {
                $row->count = (int) $row->count;
                $row->duration = (int) $row->duration;

                return $row;
            });
    }
}
