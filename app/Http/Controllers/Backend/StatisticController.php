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
    private static function diffSeconds(string $from, string $to): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST((julianday($to) - julianday($from)) * 86400 AS INTEGER)"
            : "TIMESTAMPDIFF(SECOND, $from, $to)";
    }

    private static function diffMinutes(string $from, string $to): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST((julianday($to) - julianday($from)) * 1440 AS INTEGER)"
            : "TIMESTAMPDIFF(MINUTE, $from, $to)";
    }

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
        $query->selectRaw('SUM(' . self::diffSeconds('train_checkins.departure', 'train_checkins.arrival') . ') AS duration');

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
                DB::raw('SUM(' . self::diffMinutes('train_checkins.departure', 'train_checkins.arrival') . ') AS duration'),
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
                DB::raw('SUM(' . self::diffMinutes('train_checkins.departure', 'train_checkins.arrival') . ') AS duration'),
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
                DB::raw('SUM(' . self::diffMinutes('departure', 'arrival') . ') AS duration'),
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
                DB::raw('SUM(' . self::diffMinutes('departure', 'arrival') . ') AS duration'),
            ])
            ->orderByDesc('duration')
            ->get()
            ->map(function ($row) {
                $row->count = (int) $row->count;
                $row->duration = (int) $row->duration;

                return $row;
            });
    }

    public static function getAdvancedSummary(User $user, Carbon $from, Carbon $until): array
    {
        $from = $from->clone()->startOfDay();
        $until = $until->clone()->endOfDay();

        if ($from->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        $summary = DB::table('train_checkins')
            ->where('train_checkins.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
            ->where('train_checkins.distance', '>', 0)
            ->whereNotNull('train_checkins.origin_stopover_id')
            ->whereNotNull('train_checkins.destination_stopover_id')
            ->selectRaw(
                'COUNT(*) AS total_checkins,'
                . ' COUNT(DISTINCT DATE(departure)) AS active_days,'
                . ' SUM(distance) AS total_distance_meters,'
                . ' AVG(distance) AS mean_distance_meters'
            )
            ->first();

        $rideSelect = [
            'train_checkins.id',
            'train_checkins.status_id',
            'train_checkins.distance',
            'train_checkins.departure',
            'hafas_trips.departure as start',
            'hafas_trips.arrival as end',
            'hafas_trips.linename',
            'hafas_trips.number',
            'operators.name as operator_name',
            'origin_station.name as origin_name',
            'destination_station.name as destination_name',
        ];

        $rideBase = DB::table('train_checkins')
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->leftJoin('operators', 'operators.id', '=', 'hafas_trips.operator_id')
            ->leftJoin('train_stations as origin_station', 'origin_station.id', '=', 'hafas_trips.origin_id')
            ->leftJoin('train_stations as destination_station', 'destination_station.id', '=', 'hafas_trips.destination_id')
            ->where('train_checkins.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
            ->where('train_checkins.distance', '>', 0)
            ->whereNotNull('train_checkins.origin_stopover_id')
            ->whereNotNull('train_checkins.destination_stopover_id')
            ->select($rideSelect);

        $longest = (clone $rideBase)->orderByDesc('train_checkins.distance')->first();
        $shortest = (clone $rideBase)->orderBy('train_checkins.distance')->first();

        $toRideArray = static function (?object $ride): ?array {
            if ($ride === null) {
                return null;
            }

            return [
                'id' => $ride->id,
                'status_id' => $ride->status_id,
                'distance_km' => round($ride->distance / 1000, 2),
                'departure' => $ride->departure,
                'start' => $ride->start,
                'end' => $ride->end,
                'linename' => $ride->linename,
                'number' => $ride->number,
                'operator' => $ride->operator_name,
                'origin' => $ride->origin_name,
                'destination' => $ride->destination_name,
            ];
        };

        return [
            'total_checkins' => (int) ($summary->total_checkins ?? 0),
            'active_days' => (int) ($summary->active_days ?? 0),
            'total_distance_km' => round(($summary->total_distance_meters ?? 0) / 1000, 2),
            'mean_distance_km' => round(($summary->mean_distance_meters ?? 0) / 1000, 2),
            'longest_ride' => $toRideArray($longest),
            'shortest_ride' => $toRideArray($shortest),
        ];
    }

    public static function getDistancePerYear(User $user): Collection
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        $expr = $isSqlite
            ? 'strftime("%Y", departure)'
            : 'YEAR(departure)';

        return DB::table('train_checkins')
            ->where('user_id', '=', $user->id)
            ->where('distance', '>', 0)
            ->groupByRaw($expr)
            ->selectRaw("$expr AS period, COUNT(*) AS checkin_count, SUM(distance) AS total_distance_meters")
            ->orderByRaw($expr)
            ->get()
            ->map(static fn ($row) => [
                'period' => (string) $row->period,
                'period_type' => 'year',
                'checkin_count' => (int) $row->checkin_count,
                'distance_km' => round($row->total_distance_meters / 1000, 2),
            ]);
    }

    public static function getDistancePerMonth(User $user, ?int $year = null): Collection
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        $expr = $isSqlite
            ? 'strftime("%Y-%m", departure)'
            : 'DATE_FORMAT(departure, "%Y-%m")';

        $query = DB::table('train_checkins')
            ->where('user_id', '=', $user->id)
            ->where('distance', '>', 0);

        if ($year !== null) {
            $query->whereYear('departure', $year);
        }

        return $query
            ->groupByRaw($expr)
            ->selectRaw("$expr AS period, COUNT(*) AS checkin_count, SUM(distance) AS total_distance_meters")
            ->orderByRaw($expr)
            ->get()
            ->map(static fn ($row) => [
                'period' => $row->period,
                'period_type' => 'month',
                'checkin_count' => (int) $row->checkin_count,
                'distance_km' => round($row->total_distance_meters / 1000, 2),
            ]);
    }

    public static function getDistancePerWeek(User $user, ?int $year = null, ?int $month = null): Collection
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        // MySQL mode 3 = ISO 8601 (Monday-based, week 1 contains Jan 4)
        $expr = $isSqlite
            ? 'strftime("%Y-W%W", departure)'
            : 'CONCAT(YEAR(departure), "-W", LPAD(WEEK(departure, 3), 2, "0"))';

        $query = DB::table('train_checkins')
            ->where('user_id', '=', $user->id)
            ->where('distance', '>', 0);

        if ($year !== null) {
            $query->whereYear('departure', $year);
        }

        if ($month !== null) {
            $query->whereMonth('departure', $month);
        }

        return $query
            ->groupByRaw($expr)
            ->selectRaw("$expr AS period, COUNT(*) AS checkin_count, SUM(distance) AS total_distance_meters")
            ->orderByRaw($expr)
            ->get()
            ->map(static fn ($row) => [
                'period' => $row->period,
                'period_type' => 'week',
                'checkin_count' => (int) $row->checkin_count,
                'distance_km' => round($row->total_distance_meters / 1000, 2),
            ]);
    }

    public static function getStatsForLastDays(User $user, int $days): array
    {
        $until = Carbon::now();
        $from = Carbon::now()->subDays($days);

        return self::getAdvancedSummary($user, $from, $until);
    }

    public static function getLastWeekStats(User $user): array
    {
        return self::getStatsForLastDays($user, 7);
    }

    public static function getLastMonthStats(User $user): array
    {
        return self::getStatsForLastDays($user, 30);
    }

    public static function getLastYearStats(User $user): array
    {
        return self::getStatsForLastDays($user, 365);
    }

    public static function getFavoriteStations(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
        $from = $from->clone()->startOfDay();
        $until = $until->clone()->endOfDay();

        $origins = DB::table('train_checkins')
            ->join('train_stopovers as sv', 'train_checkins.origin_stopover_id', '=', 'sv.id')
            ->where('train_checkins.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
            ->whereNotNull('train_checkins.origin_stopover_id')
            ->select('sv.train_station_id as station_id');

        $destinations = DB::table('train_checkins')
            ->join('train_stopovers as sv', 'train_checkins.destination_stopover_id', '=', 'sv.id')
            ->where('train_checkins.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
            ->whereNotNull('train_checkins.destination_stopover_id')
            ->select('sv.train_station_id as station_id');

        $combined = $origins->unionAll($destinations);

        return DB::table(DB::raw("({$combined->toSql()}) as combined"))
            ->mergeBindings($combined)
            ->join('train_stations', 'combined.station_id', '=', 'train_stations.id')
            ->groupBy('combined.station_id', 'train_stations.name')
            ->select(['combined.station_id', 'train_stations.name', DB::raw('COUNT(*) AS visit_count')])
            ->orderByDesc('visit_count')
            ->limit($limit)
            ->get()
            ->map(static fn ($row) => [
                'station_id' => (int) $row->station_id,
                'name' => $row->name,
                'count' => (int) $row->visit_count,
            ]);
    }

    public static function getFavoriteLines(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
        $from = $from->clone()->startOfDay();
        $until = $until->clone()->endOfDay();

        return DB::table('train_checkins')
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->where('train_checkins.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
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
            ->map(static fn ($row) => [
                'linename' => $row->linename,
                'number' => $row->number,
                'count' => (int) $row->count,
                'distance_km' => round(($row->total_distance_meters ?? 0) / 1000, 2),
            ]);
    }

    public static function getFavoriteRoutes(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
        $from = $from->clone()->startOfDay();
        $until = $until->clone()->endOfDay();

        return DB::table('train_checkins')
            ->join('train_stopovers as orig_sv', 'train_checkins.origin_stopover_id', '=', 'orig_sv.id')
            ->join('train_stations as orig_st', 'orig_sv.train_station_id', '=', 'orig_st.id')
            ->join('train_stopovers as dest_sv', 'train_checkins.destination_stopover_id', '=', 'dest_sv.id')
            ->join('train_stations as dest_st', 'dest_sv.train_station_id', '=', 'dest_st.id')
            ->where('train_checkins.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
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
            ->map(static fn ($row) => [
                'origin_id' => (int) $row->origin_id,
                'origin' => $row->origin_name,
                'destination_id' => (int) $row->destination_id,
                'destination' => $row->destination_name,
                'count' => (int) $row->count,
                'distance_km' => round(($row->total_distance_meters ?? 0) / 1000, 2),
            ]);
    }
}
