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

    /**
     * Get distance and count summary for a user within a date range
     * @api v1
     */
    public static function getAdvancedSummary(
        User $user,
        Carbon $from,
        Carbon $until
    ): array {
        $from->startOfDay();
        $until->endOfDay();

        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        $summary = DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.departure', '>=', $from->toIso8601String())
            ->where('train_checkins.departure', '<=', $until->toIso8601String())
            ->where('train_checkins.distance', '>', 0)
            ->select([
                DB::raw('COUNT(*) AS total_checkins'),
                DB::raw('COUNT(DISTINCT DATE(train_checkins.departure)) AS active_days'),
                DB::raw('SUM(train_checkins.distance) AS total_distance_meters'),
                DB::raw('AVG(train_checkins.distance) AS mean_distance_meters'),
                DB::raw('MAX(train_checkins.distance) AS max_distance'),
                DB::raw('MIN(train_checkins.distance) AS min_distance'),
            ])
            ->first();

        $longest = DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->leftJoin('operators', 'operators.id', '=', 'hafas_trips.operator_id')
            ->leftJoin('train_stations as origin_station', 'origin_station.id', '=', 'hafas_trips.origin_id')
            ->leftJoin('train_stations as destination_station', 'destination_station.id', '=', 'hafas_trips.destination_id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.departure', '>=', $from->toIso8601String())
            ->where('train_checkins.departure', '<=', $until->toIso8601String())
            ->where('train_checkins.distance', '>', 0)
            ->orderByDesc('train_checkins.distance')
            ->select([
                'train_checkins.id',
                'train_checkins.distance',
                'train_checkins.departure',
                'hafas_trips.departure as start',
                'hafas_trips.arrival as end',
                'hafas_trips.linename',
                'hafas_trips.number',
                'operators.name as operator_name',
                'origin_station.name as origin_name',
                'destination_station.name as destination_name',
            ])
            ->first();

        $shortest = DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->leftJoin('operators', 'operators.id', '=', 'hafas_trips.operator_id')
            ->leftJoin('train_stations as origin_station', 'origin_station.id', '=', 'hafas_trips.origin_id')
            ->leftJoin('train_stations as destination_station', 'destination_station.id', '=', 'hafas_trips.destination_id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.departure', '>=', $from->toIso8601String())
            ->where('train_checkins.departure', '<=', $until->toIso8601String())
            ->where('train_checkins.distance', '>', 0)
            ->orderBy('train_checkins.distance')
            ->select([
                'train_checkins.id',
                'train_checkins.distance',
                'train_checkins.departure',
                'hafas_trips.departure as start',
                'hafas_trips.arrival as end',
                'hafas_trips.linename',
                'hafas_trips.number',
                'operators.name as operator_name',
                'origin_station.name as origin_name',
                'destination_station.name as destination_name',
            ])
            ->first();

        return [
            'total_checkins' => (int) ($summary->total_checkins ?? 0),
            'active_days' => (int) ($summary->active_days ?? 0),
            'total_distance_km' => round(($summary->total_distance_meters ?? 0) / 1000, 2),
            'mean_distance_km' => round(($summary->mean_distance_meters ?? 0) / 1000, 2),
            'longest_ride' => $longest ? [
                'id' => $longest->id,
                'distance_km' => round($longest->distance / 1000, 2),
                'departure' => $longest->departure,
                'start' => $longest->start,
                'end' => $longest->end,
                'linename' => $longest->linename,
                'number' => $longest->number,
                'operator' => $longest->operator_name,
                'origin' => $longest->origin_name,
                'destination' => $longest->destination_name,
            ] : null,
            'shortest_ride' => $shortest ? [
                'id' => $shortest->id,
                'distance_km' => round($shortest->distance / 1000, 2),
                'departure' => $shortest->departure,
                'start' => $shortest->start,
                'end' => $shortest->end,
                'linename' => $shortest->linename,
                'number' => $shortest->number,
                'operator' => $shortest->operator_name,
                'origin' => $shortest->origin_name,
                'destination' => $shortest->destination_name,
            ] : null,
        ];
    }

    /**
     * Get distance and checkin counts aggregated by year
     * @api v1
     */
    public static function getDistancePerYear(User $user): Collection
    {
        $driver = DB::getDriverName();
        $periodExpression = $driver === 'sqlite'
            ? 'strftime("%Y", train_checkins.departure)'
            : 'YEAR(train_checkins.departure)';
        $periodSelect = DB::raw($periodExpression . ' AS year');

        return DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.distance', '>', 0)
            ->groupBy(DB::raw($periodExpression))
            ->select([
                $periodSelect,
                DB::raw('COUNT(*) AS checkin_count'),
                DB::raw('SUM(train_checkins.distance) AS total_distance_meters'),
            ])
            ->orderBy('year')
            ->get()
            ->map(function ($row) {
                return [
                    'period' => (string) $row->year,
                    'period_type' => 'year',
                    'checkin_count' => (int) $row->checkin_count,
                    'distance_km' => round($row->total_distance_meters / 1000, 2),
                ];
            });
    }

    /**
     * Get distance and checkin counts aggregated by month
     * @api v1
     */
    public static function getDistancePerMonth(User $user, ?int $year = null): Collection
    {
        $query = DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.distance', '>', 0);

        if ($year !== null) {
            $query->whereYear('train_checkins.departure', $year);
        }

        $driver = DB::getDriverName();
        $periodExpression = $driver === 'sqlite'
            ? 'strftime("%Y-%m", train_checkins.departure)'
            : 'DATE_FORMAT(train_checkins.departure, "%Y-%m")';
        $periodSelect = DB::raw($periodExpression . ' AS period');

        return $query
            ->groupBy(DB::raw($periodExpression))
            ->select([
                $periodSelect,
                DB::raw('COUNT(*) AS checkin_count'),
                DB::raw('SUM(train_checkins.distance) AS total_distance_meters'),
            ])
            ->orderBy('period')
            ->get()
            ->map(function ($row) {
                return [
                    'period' => $row->period,
                    'period_type' => 'month',
                    'checkin_count' => (int) $row->checkin_count,
                    'distance_km' => round($row->total_distance_meters / 1000, 2),
                ];
            });
    }

    /**
     * Get distance and checkin counts aggregated by week
     * @api v1
     */
    public static function getDistancePerWeek(User $user, ?int $year = null, ?int $month = null): Collection
    {
        $query = DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.distance', '>', 0);

        if ($year !== null) {
            $query->whereYear('train_checkins.departure', $year);
        }

        if ($month !== null) {
            $query->whereMonth('train_checkins.departure', $month);
        }

        $driver = DB::getDriverName();
        $periodExpression = $driver === 'sqlite'
            ? 'strftime("%Y-W%W", train_checkins.departure)'
            : 'CONCAT(YEAR(train_checkins.departure), "-W", LPAD(WEEK(train_checkins.departure), 2, "0"))';
        $periodSelect = DB::raw($periodExpression . ' AS period');

        return $query
            ->groupBy(DB::raw($periodExpression))
            ->select([
                $periodSelect,
                DB::raw('COUNT(*) AS checkin_count'),
                DB::raw('SUM(train_checkins.distance) AS total_distance_meters'),
            ])
            ->orderBy('period')
            ->get()
            ->map(function ($row) {
                return [
                    'period' => $row->period,
                    'period_type' => 'week',
                    'checkin_count' => (int) $row->checkin_count,
                    'distance_km' => round($row->total_distance_meters / 1000, 2),
                ];
            });
    }

    /**
     * Get summary statistics for last N days
     * @api v1
     */
    public static function getStatsForLastDays(User $user, int $days): array
    {
        $until = Carbon::now();
        $from = $until->clone()->subDays($days);

        return self::getAdvancedSummary($user, $from, $until);
    }

    /**
     * Get summary statistics for last week (7 days)
     * @api v1
     */
    public static function getLastWeekStats(User $user): array
    {
        return self::getStatsForLastDays($user, 7);
    }

    /**
     * Get summary statistics for last month (30 days)
     * @api v1
     */
    public static function getLastMonthStats(User $user): array
    {
        return self::getStatsForLastDays($user, 30);
    }

    /**
     * Get summary statistics for last year (365 days)
     * @api v1
     */
    public static function getLastYearStats(User $user): array
    {
        return self::getStatsForLastDays($user, 365);
    }

    /**
     * Get most visited stations (as origin or destination) for a user.
     * @api v1
     */
    public static function getFavoriteStations(
        User $user,
        Carbon $from,
        Carbon $until,
        int $limit = 10
    ): Collection {
        $from->startOfDay();
        $until->endOfDay();

        $destinations = DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('train_stopovers as sv', 'train_checkins.destination_stopover_id', '=', 'sv.id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.departure', '>=', $from->toIso8601String())
            ->where('train_checkins.departure', '<=', $until->toIso8601String())
            ->whereNotNull('train_checkins.destination_stopover_id')
            ->select('sv.train_station_id as station_id');

        $allStations = DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('train_stopovers as sv', 'train_checkins.origin_stopover_id', '=', 'sv.id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.departure', '>=', $from->toIso8601String())
            ->where('train_checkins.departure', '<=', $until->toIso8601String())
            ->whereNotNull('train_checkins.origin_stopover_id')
            ->select('sv.train_station_id as station_id')
            ->unionAll($destinations);

        return DB::table(DB::raw("({$allStations->toSql()}) as combined"))
            ->mergeBindings($allStations)
            ->join('train_stations', 'combined.station_id', '=', 'train_stations.id')
            ->groupBy('combined.station_id', 'train_stations.name')
            ->select([
                'combined.station_id',
                'train_stations.name',
                DB::raw('COUNT(*) AS visit_count'),
            ])
            ->orderByDesc('visit_count')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'station_id' => (int) $row->station_id,
                    'name'       => $row->name,
                    'count'      => (int) $row->visit_count,
                ];
            });
    }

    /**
     * Get most used train lines (by linename) for a user.
     * @api v1
     */
    public static function getFavoriteLines(
        User $user,
        Carbon $from,
        Carbon $until,
        int $limit = 10
    ): Collection {
        $from->startOfDay();
        $until->endOfDay();

        return DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.departure', '>=', $from->toIso8601String())
            ->where('train_checkins.departure', '<=', $until->toIso8601String())
            ->whereNotNull('hafas_trips.linename')
            ->groupBy('hafas_trips.linename', 'hafas_trips.number')
            ->select([
                'hafas_trips.linename',
                'hafas_trips.number',
                DB::raw('COUNT(*) AS count'),
                DB::raw('SUM(train_checkins.distance) AS total_distance_meters'),
            ])
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'linename'    => $row->linename,
                    'number'      => $row->number,
                    'count'       => (int) $row->count,
                    'distance_km' => round(($row->total_distance_meters ?? 0) / 1000, 2),
                ];
            });
    }

    /**
     * Get most used origin→destination station pairs for a user.
     * @api v1
     */
    public static function getFavoriteRoutes(
        User $user,
        Carbon $from,
        Carbon $until,
        int $limit = 10
    ): Collection {
        $from->startOfDay();
        $until->endOfDay();

        return DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('train_stopovers as orig_sv', 'train_checkins.origin_stopover_id', '=', 'orig_sv.id')
            ->join('train_stations as orig_st', 'orig_sv.train_station_id', '=', 'orig_st.id')
            ->join('train_stopovers as dest_sv', 'train_checkins.destination_stopover_id', '=', 'dest_sv.id')
            ->join('train_stations as dest_st', 'dest_sv.train_station_id', '=', 'dest_st.id')
            ->where('statuses.user_id', '=', $user->id)
            ->where('train_checkins.departure', '>=', $from->toIso8601String())
            ->where('train_checkins.departure', '<=', $until->toIso8601String())
            ->whereNotNull('train_checkins.origin_stopover_id')
            ->whereNotNull('train_checkins.destination_stopover_id')
            ->groupBy('orig_sv.train_station_id', 'dest_sv.train_station_id', 'orig_st.name', 'dest_st.name')
            ->select([
                'orig_sv.train_station_id as origin_id',
                'orig_st.name as origin_name',
                'dest_sv.train_station_id as destination_id',
                'dest_st.name as destination_name',
                DB::raw('COUNT(*) AS count'),
                DB::raw('SUM(train_checkins.distance) AS total_distance_meters'),
            ])
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'origin_id'      => (int) $row->origin_id,
                    'origin'         => $row->origin_name,
                    'destination_id' => (int) $row->destination_id,
                    'destination'    => $row->destination_name,
                    'count'          => (int) $row->count,
                    'distance_km'    => round(($row->total_distance_meters ?? 0) / 1000, 2),
                ];
            });
    }
}
