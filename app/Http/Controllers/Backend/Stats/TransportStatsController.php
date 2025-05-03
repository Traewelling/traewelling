<?php

namespace App\Http\Controllers\Backend\Stats;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use stdClass;

abstract class TransportStatsController extends Controller
{

    private static function getTrainCheckinsBetween(User $user, Carbon $from, Carbon $to, bool $returnModel = false): QueryBuilder|EloquentBuilder {
        return ($returnModel ? Checkin::with([]) : DB::table('train_checkins'))
            ->where('train_checkins.user_id', $user->id)
            ->whereBetween('train_checkins.departure', [$from, $to]);
    }

    /**
     * Get the number of train checkins between two dates.
     *
     * @param User   $user The user to get the checkins for.
     * @param Carbon $from The start date.
     * @param Carbon $to   The end date.
     *
     * @return int        The number of checkins.
     */
    public static function count(User $user, Carbon $from, Carbon $to): int {
        return (int) self::getTrainCheckinsBetween($user, $from, $to)
                         ->select([DB::raw('COUNT(*) as count')])
                         ->pluck('count')
                         ->first();
    }

    /**
     * Get travelled distance and duration for a user in a given time frame
     *
     * @param User   $user
     * @param Carbon $from
     * @param Carbon $to
     *
     * @return stdClass with properties distance (in meters) and duration (in minutes)
     */
    public static function sum(User $user, Carbon $from, Carbon $to): stdClass {
        return self::getTrainCheckinsBetween($user, $from, $to)
                   ->select([
                                DB::raw('SUM(distance) as distance'),
                                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, departure, arrival)) as duration'),
                            ])
                   ->first();
    }

    /**
     * Get travelled distance and duration for a user in a given time frame, grouped by HAFAS operator
     *
     * @param User     $user        User to get the stats for
     * @param Carbon   $from        Start date
     * @param Carbon   $to          End date
     * @param string   $orderByDesc Column to order by, descending. Must be 'distance' or 'duration'
     * @param int|null $limit       Limit the number of results
     *
     * @return Collection with stdClass objects with properties hafasOperator (string), distance (int), duration (int)
     */
    public static function sumByHafasOperator(User $user, Carbon $from, Carbon $to, string $orderByDesc = 'distance', int $limit = null): Collection {
        if ($orderByDesc !== 'distance' && $orderByDesc !== 'duration') {
            throw new InvalidArgumentException('orderByDesc must be either "distance" or "duration"');
        }
        return self::getTrainCheckinsBetween($user, $from, $to)
                   ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                   ->join('hafas_operators', 'hafas_trips.operator_id', '=', 'hafas_operators.id')
                   ->groupBy('hafas_operators.name')
                   ->select([
                                'hafas_operators.name',
                                DB::raw('SUM(train_checkins.distance) as distance'),
                                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, train_checkins.departure, train_checkins.arrival)) as duration'),
                            ])
                   ->orderByDesc($orderByDesc)
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get count of different hafas operators in a given time frame
     *
     * @param User   $user User to get the stats for
     * @param Carbon $from Start date
     * @param Carbon $to   End date
     *
     * @return int      Number of different hafas operators
     */
    public static function countHafasOperators(User $user, Carbon $from, Carbon $to): int {
        return self::sumByHafasOperator($user, $from, $to)->count();
    }

    /**
     * Get travelled distance and duration for a user in a given time frame, grouped by HAFAS operator and line
     *
     * @param User     $user        User to get the stats for
     * @param Carbon   $from        Start date
     * @param Carbon   $to          End date
     * @param string   $orderByDesc Column to order by, descending. Must be 'distance' or 'duration'
     * @param int|null $limit       Limit the number of results
     *
     * @return Collection with stdClass objects with properties hafasOperator (string), hafasLine (string), distance
     *                    (int), duration (int)
     */
    public static function sumByHafasOperatorAndLine(User $user, Carbon $from, Carbon $to, string $orderByDesc = 'distance', int $limit = null): Collection {
        if ($orderByDesc !== 'distance' && $orderByDesc !== 'duration') {
            throw new InvalidArgumentException('orderByDesc must be either "distance" or "duration"');
        }
        return self::getTrainCheckinsBetween($user, $from, $to)
                   ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                   ->join('hafas_operators', 'hafas_trips.operator_id', '=', 'hafas_operators.id')
                   ->groupBy('hafas_operators.name')
                   ->groupBy('hafas_trips.linename')
                   ->select([
                                'hafas_operators.name',
                                'hafas_trips.linename',
                                DB::raw('SUM(train_checkins.distance) as distance'),
                                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, train_checkins.departure, train_checkins.arrival)) as duration'),
                            ])
                   ->orderByDesc($orderByDesc)
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get the longest trips (distance or duration) for a user in a given time frame
     *
     * @param User   $user        User to get the stats for
     * @param Carbon $from        Start date
     * @param Carbon $to          End date
     * @param string $orderByDesc Column to order by, descending. Must be 'distance' or 'duration'
     * @param int    $limit       Limit the number of results
     *
     * @return Collection   with stdClass objects with properties hafasOperator (string), hafasLine (string),
     *                      train_checkin (raw!)
     */
    public static function getLongestTrips(User $user, Carbon $from, Carbon $to, string $orderByDesc = 'distance', int $limit = 10): Collection {
        if ($orderByDesc !== 'distance' && $orderByDesc !== 'duration') {
            throw new InvalidArgumentException('orderByDesc must be either "distance" or "duration"');
        }
        $query = self::getTrainCheckinsBetween($user, $from, $to, true)
                     ->select([
                                  'train_checkins.*',
                                  DB::raw('TIMESTAMPDIFF(MINUTE, train_checkins.departure, train_checkins.arrival) as duration'),
                              ])
                     ->limit($limit);
        if ($orderByDesc === 'distance') {
            $query->orderByDesc('distance');
        } else {
            $query->orderByDesc(DB::raw('TIMESTAMPDIFF(MINUTE, train_checkins.departure, train_checkins.arrival)'));
        }
        return $query->get();
    }

    /**
     *  Get the fastest/slowest trips for a user in a given time frame
     *
     * @param User   $user   User to get the stats for
     * @param Carbon $from   Start date
     * @param Carbon $to     End date
     * @param string $sortBy If order should be ascending or descending
     * @param int    $limit  Limit the number of results
     *
     * @return Collection with stdClass objects with properties train_checkin (raw!), duration and kmh
     */
    public static function getTripsBySpeed(User $user, Carbon $from, Carbon $to, string $sortBy = 'desc', int $limit = 10): Collection {
        if ($sortBy !== 'desc' && $sortBy !== 'asc') {
            throw new InvalidArgumentException('sortBy must be either "desc" or "asc"');
        }
        return self::getTrainCheckinsBetween($user, $from, $to, true)
                   ->whereNotNull(DB::raw('(train_checkins.distance/1000) / TIMESTAMPDIFF(HOUR, train_checkins.departure, train_checkins.arrival)'))
                   ->orderBy(DB::raw('(train_checkins.distance/1000) / TIMESTAMPDIFF(HOUR, train_checkins.departure, train_checkins.arrival)'), $sortBy)
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get the trips with the most arrival delays for a user in a given time frame
     *
     * @param User   $user   User to get the stats for
     * @param Carbon $from   Start date
     * @param Carbon $to     End date
     * @param string $sortBy If order should be ascending or descending
     * @param int    $limit  Limit the number of results
     *
     * @return Collection with stdClass objects with properties train_checkin (raw!) and delay
     */
    public static function getTripsByArrivalDelay(User $user, Carbon $from, Carbon $to, string $sortBy = 'desc', int $limit = 10): Collection {
        if ($sortBy !== 'desc' && $sortBy !== 'asc') {
            throw new InvalidArgumentException('sortBy must be either "desc" or "asc"');
        }
        return self::getTrainCheckinsBetween($user, $from, $to, true)
                   ->join('train_stopovers', 'train_checkins.destination_stopover_id', '=', 'train_stopovers.id')
                   ->select([
                                'train_checkins.*',
                                DB::raw('TIMESTAMPDIFF(MINUTE, train_stopovers.arrival_planned, train_stopovers.arrival_real) as delay'),
                            ])
                   ->orderBy('delay', $sortBy)
                   ->limit($limit)
                   ->get();
    }

    /**
     *  Get the total sum of arrival delay in the given time frame
     *
     * @param User   $user User to get the stats for
     * @param Carbon $from Start date
     * @param Carbon $to   End date
     *
     * @return int  Total sum of arrival delay in minutes
     */
    public static function getTotalArrivalDelay(User $user, Carbon $from, Carbon $to): int {
        return self::getTrainCheckinsBetween($user, $from, $to, true)
                   ->join('train_stopovers', 'train_checkins.destination_stopover_id', '=', 'train_stopovers.id')
                   ->select([
                                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, train_stopovers.arrival_planned, train_stopovers.arrival_real)) as delay'),
                            ])
                   ->first()?->delay ?? 0;
    }

    public static function getTopDestinations(User $user, Carbon $from, Carbon $to, int $limit = null): Collection {
        $data     = self::getTrainCheckinsBetween($user, $from, $to)
                        ->join('train_stopovers', 'train_checkins.destination_stopover_id', '=', 'train_stopovers.id')
                        ->join('train_stations', 'train_stopovers.train_station_id', '=', 'train_stations.id')
                        ->groupBy('train_stations.id')
                        ->select([
                                     'train_stations.id',
                                     DB::raw('COUNT(*) as count'),
                                 ])
                        ->orderByDesc('count')
                        ->limit($limit)
                        ->get();
        $stations = Station::whereIn('id', $data->pluck('id'))->get();
        return $data->map(function($model) use ($stations) {
            $model->station = $stations->firstWhere('id', $model->id);
            unset($model->destination);
            return $model;
        });
    }

    /**
     * Get all stations where the user is the only one to have checked in
     *
     * @param User   $user User to get the stats for
     * @param Carbon $from Start date
     * @param Carbon $to   End date
     *
     * @return Collection
     */
    public static function getLonelyStations(User $user, Carbon $from, Carbon $to): Collection {
        $ownDestinations = self::getTrainCheckinsBetween($user, $from, $to)
                               ->join('train_stopovers', 'train_checkins.destination_stopover_id', '=', 'train_stopovers.id')
                               ->join('train_stations', 'train_stopovers.train_station_id', '=', 'train_stations.id')
                               ->groupBy('train_stations.id')
                               ->select([
                                            'train_stations.id',
                                            DB::raw('COUNT(*) as count'),
                                        ])
                               ->distinct()
                               ->get()
                               ->map(function($row) {
                                   $row->otherUsers = 0;
                                   return $row;
                               });

        $otherUsers = DB::table('train_checkins')
                        ->join('train_stopovers', 'train_checkins.destination_stopover_id', '=', 'train_stopovers.id')
                        ->join('train_stations', 'train_stopovers.train_station_id', '=', 'train_stations.id')
                        ->where('user_id', '!=', $user->id)
                        ->whereBetween('departure', [$from, $to])
                        ->whereIn('train_stations.id', $ownDestinations->pluck('id'))
                        ->groupBy('train_stations.id')
                        ->select([
                                     'train_stations.id',
                                     DB::raw('COUNT(*) as count'),
                                 ])
                        ->get();

        foreach ($otherUsers as $other) {
            $ownDestinations->firstWhere('id', $other->id)->otherUsers = $other->count;
        }

        $lonelyStations = $ownDestinations->where('otherUsers', 0)->pluck('id');

        return Station::whereIn('id', $lonelyStations)->get()
                      ->map(function($station) use ($ownDestinations) {
                          $station->count = $ownDestinations->firstWhere('id', $station->id)->count;
                          return $station;
                      });
    }

    public static function getMostLikedStatus(User $user, Carbon $from, Carbon $to, int $limit = 3): Collection {
        if ($limit < 1) {
            throw new InvalidArgumentException('limit must be greater than 0');
        }
        if ($limit > 100) {
            throw new InvalidArgumentException('limit must be smaller than 10');
        }
        return self::getTrainCheckinsBetween($user, $from, $to, true)
                   ->join('likes', 'train_checkins.status_id', '=', 'likes.status_id')
                   ->groupBy('train_checkins.status_id')
                   ->select([
                                'train_checkins.status_id',
                                DB::raw('COUNT(*) as count'),
                            ])
                   ->orderByDesc('count')
                   ->limit($limit)
                   ->get()
                   ->map(function($row) {
                       return [
                           'status'    => $row->status,
                           'likeCount' => $row->count,
                       ];
                   });
    }
}
