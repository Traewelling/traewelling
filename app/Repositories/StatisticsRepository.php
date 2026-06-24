<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\Internal\GlobalCheckinStats;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsRepository
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

    private function checkinBase(User $user, Carbon $from, Carbon $until): Builder
    {
        return DB::table('train_checkins')
            ->where('train_checkins.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
            ->where('train_checkins.distance', '>', 0)
            ->whereNotNull('train_checkins.origin_stopover_id')
            ->whereNotNull('train_checkins.destination_stopover_id');
    }

    public function globalStats(?Carbon $from = null, ?Carbon $until = null): GlobalCheckinStats
    {
        $query = DB::table('train_checkins');

        if ($from !== null && $until !== null) {
            $query->whereBetween('train_checkins.departure', [$from, $until]);
        }

        $query->selectRaw('SUM(train_checkins.distance) AS distance')
            ->selectRaw('COUNT(DISTINCT train_checkins.user_id) AS userCount')
            ->selectRaw('SUM(' . self::diffSeconds('train_checkins.departure', 'train_checkins.arrival') . ') AS duration');

        $result = $query->first();

        return new GlobalCheckinStats(
            (int) ($result->distance ?? 0),
            (int) ($result->duration ?? 0),
            (int) ($result->userCount ?? 0),
        );
    }

    public function travelPurposes(User $user, Carbon $from, Carbon $until): Collection
    {
        return DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('statuses.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
            ->groupBy('statuses.business')
            ->select([
                DB::raw('statuses.business AS reason'),
                DB::raw('COUNT(*) AS count'),
                DB::raw('SUM(' . self::diffMinutes('departure', 'arrival') . ') AS duration'),
            ])
            ->orderByDesc('duration')
            ->get()
            ->map(static function ($row): object {
                $row->count = (int) $row->count;
                $row->duration = (int) $row->duration;

                return $row;
            });
    }

    public function travelCategories(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
        return DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->where('statuses.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
            ->groupBy('hafas_trips.category')
            ->select([
                'hafas_trips.category AS name',
                DB::raw('COUNT(*) AS count'),
                DB::raw('SUM(' . self::diffMinutes('train_checkins.departure', 'train_checkins.arrival') . ') AS duration'),
            ])
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit($limit)
            ->get()
            ->map(static function ($row): object {
                $row->count = (int) $row->count;
                $row->duration = (int) $row->duration;

                return $row;
            });
    }

    public function tripOperators(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
        return DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->leftJoin('operators', 'operators.id', '=', 'hafas_trips.operator_id')
            ->where('statuses.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
            ->groupBy('operators.name')
            ->select([
                'operators.name',
                DB::raw('COUNT(*) AS count'),
                DB::raw('SUM(' . self::diffMinutes('train_checkins.departure', 'train_checkins.arrival') . ') AS duration'),
            ])
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit($limit)
            ->get()
            ->map(static function ($row): object {
                $row->count = (int) $row->count;
                $row->duration = (int) $row->duration;

                return $row;
            });
    }

    public function dailyTravelTime(User $user, Carbon $from, Carbon $until): Collection
    {
        return DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('statuses.user_id', '=', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $until])
            ->groupBy(DB::raw('DATE(train_checkins.departure)'))
            ->select([
                DB::raw('DATE(train_checkins.departure) AS date'),
                DB::raw('COUNT(*) AS count'),
                DB::raw('SUM(' . self::diffMinutes('departure', 'arrival') . ') AS duration'),
            ])
            ->orderBy(DB::raw('date'))
            ->get();
    }

    public function checkinAggregate(User $user, Carbon $from, Carbon $until): ?object
    {
        return $this->checkinBase($user, $from, $until)
            ->selectRaw(
                'COUNT(*) AS total_checkins,'
                . ' COUNT(DISTINCT DATE(departure)) AS active_days,'
                . ' SUM(distance) AS total_distance_meters,'
                . ' AVG(distance) AS mean_distance_meters'
            )
            ->first();
    }

    public function longestRideStatusId(User $user, Carbon $from, Carbon $until): ?int
    {
        return $this->checkinBase($user, $from, $until)
            ->orderByDesc('train_checkins.distance')
            ->orderByDesc('train_checkins.id')
            ->value('train_checkins.status_id');
    }

    public function shortestRideStatusId(User $user, Carbon $from, Carbon $until): ?int
    {
        return $this->checkinBase($user, $from, $until)
            ->orderBy('train_checkins.distance')
            ->orderBy('train_checkins.id')
            ->value('train_checkins.status_id');
    }

    public function longestRideByDurationStatusId(User $user, Carbon $from, Carbon $until): ?int
    {
        return $this->checkinBase($user, $from, $until)
            ->orderByDesc(DB::raw(self::diffSeconds('train_checkins.departure', 'train_checkins.arrival')))
            ->orderByDesc('train_checkins.id')
            ->value('train_checkins.status_id');
    }

    public function shortestRideByDurationStatusId(User $user, Carbon $from, Carbon $until): ?int
    {
        return $this->checkinBase($user, $from, $until)
            ->orderBy(DB::raw(self::diffSeconds('train_checkins.departure', 'train_checkins.arrival')))
            ->orderBy('train_checkins.id')
            ->value('train_checkins.status_id');
    }

    public function distanceByYear(User $user): Collection
    {
        $expr = DB::getDriverName() === 'sqlite'
            ? 'strftime("%Y", departure)'
            : 'YEAR(departure)';

        return DB::table('train_checkins')
            ->where('user_id', '=', $user->id)
            ->where('distance', '>', 0)
            ->groupByRaw($expr)
            ->selectRaw("$expr AS period, COUNT(*) AS checkin_count, SUM(distance) AS total_distance_meters")
            ->orderByRaw($expr)
            ->get();
    }

    public function distanceByMonth(User $user, ?int $year = null): Collection
    {
        $expr = DB::getDriverName() === 'sqlite'
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
            ->get();
    }

    public function distanceByWeek(User $user, ?int $year = null, ?int $month = null): Collection
    {
        $expr = DB::getDriverName() === 'sqlite'
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
            ->get();
    }

    public function favoriteStations(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
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
            ->get();
    }

    public function favoriteLines(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
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
            ->get();
    }

    public function favoriteRoutes(User $user, Carbon $from, Carbon $until, int $limit = 10): Collection
    {
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
            ->get();
    }
}
