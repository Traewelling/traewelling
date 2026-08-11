<?php

namespace App\Http\Controllers\Backend\Stats;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailyStatsController extends Controller
{
    /**
     * Returns the most recent date before $date on which the user has at least one check-in,
     * converted to the user's timezone. Returns null if no earlier check-in exists.
     */
    public static function getPrevDateWithStatuses(User $user, Carbon $date): ?Carbon
    {
        $startOfDay = $date->clone()->startOfDay()->tz('UTC');

        $row = DB::table('train_checkins')
            ->join('statuses', 'statuses.id', '=', 'train_checkins.status_id')
            ->where('statuses.user_id', $user->id)
            ->where('train_checkins.departure', '<', $startOfDay)
            ->orderByDesc('train_checkins.departure')
            ->select('train_checkins.departure')
            ->first();

        if ($row === null) {
            return null;
        }

        return Carbon::parse($row->departure)->tz($user->timezone)->startOfDay();
    }

    /**
     * Returns the nearest future date after $date on which the user has at least one check-in,
     * converted to the user's timezone. Returns null if no later check-in exists (or it is in the future).
     */
    public static function getNextDateWithStatuses(User $user, Carbon $date): ?Carbon
    {
        $endOfDay = $date->clone()->endOfDay()->tz('UTC');
        $nowUtc = Carbon::now()->tz('UTC');

        $row = DB::table('train_checkins')
            ->join('statuses', 'statuses.id', '=', 'train_checkins.status_id')
            ->where('statuses.user_id', $user->id)
            ->where('train_checkins.departure', '>', $endOfDay)
            ->where('train_checkins.departure', '<=', $nowUtc)
            ->orderBy('train_checkins.departure')
            ->select('train_checkins.departure')
            ->first();

        if ($row === null) {
            return null;
        }

        return Carbon::parse($row->departure)->tz($user->timezone)->startOfDay();
    }

    public static function getStatusesOnDate(User $user, Carbon $date): Collection
    {
        $start = $date->clone()->startOfDay()->tz('UTC');
        $end = $date->clone()->endOfDay()->tz('UTC');

        return Status::with([
            'checkin.originStopover.station',
            'checkin.destinationStopover.station',
            'tags',
        ])
            ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
            ->where('statuses.user_id', $user->id)
            ->where('train_checkins.departure', '>=', $start)
            ->where('train_checkins.departure', '<=', $end)
            ->select('statuses.*')
            ->get()
            ->sortBy('checkin.departure');
    }
}
