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
                             $trustedUserIds = self::getTrustedUserIds($user->id);
                             if (!empty($trustedUserIds)) {
                                 $trustedQuery->where('statuses.visibility', StatusVisibility::TRUSTED->value)
                                             ->whereIn('statuses.user_id', $trustedUserIds);
                             }
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

    /**
     * Get user IDs who trust the given user (cached for performance)
     *
     * @param int $viewingUserId
     * @return array
     */
    private static function getTrustedUserIds(int $viewingUserId): array {
        return \Cache::remember("trusted_users_{$viewingUserId}", now()->addMinutes(5), function() use ($viewingUserId) {
            return DB::table('trusted_users')
                ->where('trusted_id', $viewingUserId)
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->pluck('user_id')
                ->toArray();
        });
    }
}
