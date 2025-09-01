<?php

namespace App\Http\Controllers\Backend\User;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\Transport\StatusController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

abstract class DashboardController extends Controller
{

    public static function getPrivateDashboard(User $user): Paginator {
        $followingIDs   = $user->follows->pluck('id');
        $followingIDs[] = $user->id;
        return Status::with([
                                'event',
                                'likes',
                                'user.blockedByUsers',
                                'user.blockedUsers',
                                'checkin',
                                'tags',
                                'mentions.mentioned',
                                'checkin.originStopover.station.names',
                                'checkin.destinationStopover.station.names',
                                'checkin.trip.stopovers.station.names'
                            ])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->select('statuses.*')
                     ->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20))
                     ->whereIn('statuses.user_id', $followingIDs)
                     ->whereNotIn('statuses.user_id', $user->mutedUsers->pluck('id'))
                     ->where(function($query) use ($user) {
                         $query->whereIn('statuses.visibility', [
                             StatusVisibility::PUBLIC->value,
                             StatusVisibility::FOLLOWERS->value,
                             StatusVisibility::AUTHENTICATED->value
                         ])
                         ->orWhere(function($trustedQuery) use ($user) {
                             $trustedQuery->where('statuses.visibility', StatusVisibility::TRUSTED->value)
                                         ->whereExists(function($subQuery) use ($user) {
                                             $subQuery->select(\DB::raw(1))
                                                     ->from('trusted_users')
                                                     ->whereColumn('trusted_users.user_id', 'statuses.user_id')
                                                     ->where('trusted_users.trusted_id', $user->id)
                                                     ->where(function($expireQuery) {
                                                         $expireQuery->whereNull('trusted_users.expires_at')
                                                                    ->orWhere('trusted_users.expires_at', '>', now());
                                                     });
                                         });
                         });
                     })
                     ->orWhere(function($query) use ($user) {
                         $query->where('statuses.user_id', $user->id)
                               ->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20));
                     })
                     ->orderBy('train_checkins.departure', 'desc')
                     ->latest()
                     ->simplePaginate(15);
    }

    public static function getGlobalDashboard(User $user): Paginator {
        return Status::with([
                                'event',
                                'likes',
                                'user.blockedByUsers',
                                'user.blockedUsers',
                                'checkin',
                                'mentions.mentioned',
                                'tags',
                                'checkin.originStopover.station.names',
                                'checkin.destinationStopover.station.names',
                                'checkin.trip.stopovers.station.names'
                            ])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->join('users', 'statuses.user_id', '=', 'users.id')
                     ->where(StatusController::filterStatusVisibility($user))
                     ->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20))
                     ->whereNotIn('statuses.user_id', $user->mutedUsers()->select('muted_id'))
                     ->whereNotIn('statuses.user_id', $user->blockedUsers()->select('blocked_id'))
                     ->whereNotIn('statuses.user_id', $user->blockedByUsers()->select('user_id'))
                     ->select('statuses.*')
                     ->orderByDesc('train_checkins.departure')
                     ->simplePaginate(15);
    }
}
