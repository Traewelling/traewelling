<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use UnexpectedValueException;

abstract class LeaderboardController extends Controller
{
    public static function getLeaderboard(
        string $orderBy = 'points',
        Carbon $since = null,
        Carbon $until = null,
        int    $limit = 20,
        bool   $onlyFollowings = false
    ): Collection {
        if (auth()->user()?->points_enabled === false) {
            return collect();
        }

        if ($since == null) {
            $since = now()->subWeek();
        }
        if ($until == null) {
            $until = now();
        }
        if ($since->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }
        if (!in_array($orderBy, ['points', 'distance', 'duration', 'speed'])) {
            throw new InvalidArgumentException(
                'orderBy must be one of the following strings: points, distance, duration, speed'
            );
        }

        $sumDistance = 'SUM(train_checkins.distance)';

        $query = DB::table('statuses')
                   ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                   ->join('users', 'statuses.user_id', '=', 'users.id')
                   ->where('train_checkins.departure', '>=', $since->toIso8601String())
                   ->where('train_checkins.departure', '<=', $until->toIso8601String())
                   ->where(function(Builder $query) {
                       $query->where('users.private_profile', 0);
                       if (auth()->check()) {
                           $query->orWhereIn('users.id', auth()->user()->follows->pluck('id'))
                                 ->orWhere('users.id', auth()->user()->id);
                       }
                   })
                   ->groupBy('statuses.user_id')
                   ->select([
                                'statuses.user_id',
                                DB::raw('SUM(train_checkins.points) AS points'),
                                DB::raw($sumDistance . ' AS distance'),
                                DB::raw(self::getDurationSelector() . ' AS duration'),
                                DB::raw($sumDistance . ' / (' . self::getDurationSelector() . ' / 60) AS speed'),
                            ])
                   ->orderByDesc($orderBy)
                   ->limit($limit);

        if ($onlyFollowings && auth()->check()) {
            $query->where(function($query) {
                $query->whereIn('statuses.user_id', auth()->user()->follows->pluck('id'))
                      ->orWhere('statuses.user_id', auth()->user()->id);
            });
        }

        $data = $query->get();

        //Fetch user models in ONE query and map it to the collection
        $userCache = User::with(['blockedByUsers', 'blockedUsers'])->whereIn('id', $data->pluck('user_id'))->get();

        return $data->map(function($row) use ($userCache) {
            $row->user = $userCache->where('id', $row->user_id)->first();
            return $row;
        });
    }

    public static function getMonthlyLeaderboard(Carbon $date): Collection {
        if (auth()->user()?->points_enabled === false) {
            return collect();
        }


        $data = DB::table('statuses')
                  ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                  ->join('users', 'statuses.user_id', '=', 'users.id')
                  ->where(
                      'train_checkins.departure',
                      '>=',
                      $date->clone()->firstOfMonth()->toIso8601String()
                  )
                  ->where(
                      'train_checkins.departure',
                      '<=',
                      $date->clone()->lastOfMonth()->endOfDay()->toIso8601String()
                  )
                  ->where(function(Builder $query) {
                      $query->where('users.private_profile', 0);
                      if (auth()->check()) {
                          $query->orWhereIn('users.id', auth()->user()->follows->pluck('id'))
                                ->orWhere('users.id', auth()->user()->id);
                      }
                  })
                  ->select([
                               'statuses.user_id',
                               DB::raw('SUM(train_checkins.points) AS points'),
                               DB::raw('SUM(train_checkins.distance) AS distance'),
                               DB::raw(self::getDurationSelector() . ' AS duration'),
                               DB::raw('SUM(train_checkins.distance) / (' . self::getDurationSelector() . ' / 60) AS speed'),
                           ])
                  ->groupBy('user_id')
                  ->orderByDesc('points')
                  ->limit(100)
                  ->get();

        //Fetch user models in ONE query and map it to the collection
        $userCache = User::whereIn('id', $data->pluck('user_id'))->get();

        return $data->map(function($row) use ($userCache) {
            $row->user = $userCache->where('id', $row->user_id)->first();
            return $row;
        });
    }

    private static function getDurationSelector(): string {
        if (config('database.default') === 'mysql') {
            return 'SUM(TIMESTAMPDIFF(MINUTE, train_checkins.departure, train_checkins.arrival))';
        }

        if (config('database.default') === 'sqlite') {
            // Sorry for this disgusting code. But we test with SQLite.
            // There are different functions than with MySQL/MariaDB.
            return 'SUM((JULIANDAY(train_checkins.arrival) - JULIANDAY(train_checkins.departure)) * 1440)';
        }

        throw new UnexpectedValueException('Driver not supported');
    }
}
