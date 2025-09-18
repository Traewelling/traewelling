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
                     ->leftJoin('trusted_users', function($join) use ($user) {
                         $join->on('trusted_users.user_id', '=', 'statuses.user_id')
                              ->where('trusted_users.trusted_id', '=', $user->id)
                              ->where(function($expireQuery) {
                                  $expireQuery->whereNull('trusted_users.expires_at')
                                             ->orWhere('trusted_users.expires_at', '>', now());
                              });
                     })
                     ->select('statuses.*')
                     ->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20))
                     ->whereIn('statuses.user_id', $followingIDs)
                     ->whereNotIn('statuses.user_id', $user->mutedUsers->pluck('id'))
                     ->where(function($query) {
                         $query->whereIn('statuses.visibility', [
                             StatusVisibility::PUBLIC->value,
                             StatusVisibility::FOLLOWERS->value,
                             StatusVisibility::AUTHENTICATED->value
                         ])
                         ->orWhere(function($trustedQuery) {
                             $trustedQuery->where('statuses.visibility', StatusVisibility::TRUSTED->value)
                                         ->whereNotNull('trusted_users.user_id'); // JOIN condition met
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
}
