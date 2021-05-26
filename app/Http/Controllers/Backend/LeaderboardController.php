<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeaderboardController extends Controller
{
    public static function getLeaderboard(
        string $orderBy = 'points',
        Carbon $since = null,
        Carbon $until = null,
        int $limit = 20,
        bool $onlyFollowings = false
    ): Collection {
        if ($since == null) {
            $since = Carbon::now()->subWeek();
        }
        if ($until == null) {
            $until = Carbon::now();
        }
        if ($since->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }
        if (!in_array($orderBy, ['points', 'distance', 'duration', 'speed'])) {
            throw new InvalidArgumentException(
                'orderBy must be one of the following strings: points, distance, duration, speed'
            );
        }

        $sumDuration = 'SUM((train_checkins.arrival - train_checkins.departure) / 60)';
        $sumDistance = 'SUM(train_checkins.distance)';

        $query = DB::table('statuses')
                   ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                   ->where('train_checkins.departure', '>=', $since->toIso8601String())
                   ->where('train_checkins.departure', '<=', $until->toIso8601String())
                   ->groupBy('statuses.user_id')
                   ->select([
                                'statuses.user_id',
                                DB::raw('SUM(train_checkins.points) AS points'),
                                DB::raw($sumDistance . ' AS distance'),
                                DB::raw($sumDuration . ' AS duration'),
                                DB::raw($sumDistance . ' / ' . $sumDuration . ' AS speed'),
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
        $userCache = User::whereIn('id', $data->pluck('user_id'))->get();

        // ToDo: Probably re-sort for new distance-calculation, etc.
        return $data->map(function($row) use ($userCache) {
            $user                 = $userCache->where('id', $row->user_id)->first();
            $user->train_distance = $row->distance;
            $user->train_duration = $row->duration;
            $user->train_speed    = $row->speed;
            $user->points         = $row->points;
            return $user;
        });
    }

    public static function getMonthlyLeaderboard(Carbon $date): Collection {
        return Status::with(['trainCheckin', 'user'])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->where(
                         'train_checkins.departure',
                         '>=',
                         $date->clone()->firstOfMonth()->toDateString()
                     )
                     ->where(
                         'train_checkins.departure',
                         '<=',
                         $date->clone()->lastOfMonth()->toDateString() . ' 23:59:59'
                     )
                     ->get()
                     ->groupBy('user_id')
                     ->map(function($statuses) {
                         $user                 = $statuses->first()->user;
                         $user->train_distance = $statuses->sum('distance');
                         $user->train_duration = $statuses->sum('trainCheckin.duration');
                         $user->train_speed    = null;
                         $user->points         = $statuses->sum('trainCheckin.points');
                         return $user;
                     })
                     ->sortByDesc('points');
    }
}
