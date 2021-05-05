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
    public static function getGlobalCheckInStats(): stdClass {
        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->where('train_checkins.departure', '>', DB::raw('(NOW() - INTERVAL 1 DAY)'))
                 ->select([
                              DB::raw('SUM(train_checkins.distance) AS distance'),
                              DB::raw('SUM(TIMESTAMPDIFF(SECOND, train_checkins.departure, train_checkins.arrival)) AS duration'),
                              DB::raw('COUNT(DISTINCT statuses.user_id) AS user_count')
                          ])
                 ->first();
    }

    public static function getTopTravelCategoryByUser(User $user, Carbon $since = null, Carbon $until = null, int $limit = 10): Collection {

        if ($since == null) {
            $since = Carbon::now()->subWeek();
        }
        if ($until == null) {
            $until = Carbon::now();
        }
        if ($since->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $since->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy('hafas_trips.category')
                 ->select(['hafas_trips.category', DB::raw('COUNT(*) AS count')])
                 ->orderByDesc(DB::raw('COUNT(*)'))
                 ->limit($limit)
                 ->get()
                 ->map(function($data) {
                     $data->category = __('transport_types.' . $data->category);
                     return $data;
                 });
    }

    public static function getTopTripOperatorByUser(User $user, Carbon $since = null, Carbon $until = null, int $limit = 10): Collection {

        if ($since == null) {
            $since = Carbon::now()->subWeek();
        }
        if ($until == null) {
            $until = Carbon::now();
        }
        if ($since->isAfter($until)) {
            throw new InvalidArgumentException('since cannot be after until');
        }

        return DB::table('train_checkins')
                 ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
                 ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                 ->join('hafas_operators', 'hafas_operators.id', '=', 'hafas_trips.operator_id')
                 ->where('statuses.user_id', '=', $user->id)
                 ->where('train_checkins.departure', '>=', $since->toIso8601String())
                 ->where('train_checkins.departure', '<=', $until->toIso8601String())
                 ->groupBy('hafas_operators.name')
                 ->select(['hafas_operators.name', DB::raw('COUNT(*) AS count')])
                 ->orderByDesc(DB::raw('COUNT(*)'))
                 ->limit($limit)
                 ->get();
    }
}
