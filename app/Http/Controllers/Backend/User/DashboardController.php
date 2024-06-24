<?php

namespace App\Http\Controllers\Backend\User;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

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
                                'checkin.originStopover.station',
                                'checkin.destinationStopover.station',
                                'checkin.trip.stopovers.station'
                            ])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->select('statuses.*')
                     ->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20))
                     ->orderBy('train_checkins.departure', 'desc')
                     ->whereIn('statuses.user_id', $followingIDs)
                     ->whereNotIn('statuses.user_id', $user->mutedUsers->pluck('id'))
                     ->whereIn('statuses.visibility', [
                         StatusVisibility::PUBLIC->value,
                         StatusVisibility::FOLLOWERS->value,
                         StatusVisibility::AUTHENTICATED->value
                     ])
                     ->orWhere('statuses.user_id', $user->id)
                     ->latest()
                     ->simplePaginate(15);
    }

    public static function getGlobalDashboard(User $user): Paginator {
        $query = Status::with([
                                  'event',
                                  'likes',
                                  'user.blockedByUsers',
                                  'user.blockedUsers',
                                  'checkin',
                                  'mentions.mentioned',
                                  'tags',
                                  'checkin.originStopover.station',
                                  'checkin.destinationStopover.station',
                                  'checkin.trip.stopovers.station'
                              ])
                       ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                       ->join('train_stopovers AS origin_stopover', 'train_checkins.origin_stopover_id', '=', 'origin_stopover.id')
                       ->join('users', 'statuses.user_id', '=', 'users.id');

        // exclude muted users
        $query->leftJoin('user_mutes', function($join) use ($user) {
            $join->on('user_mutes.muted_id', '=', 'users.id')
                 ->where('user_mutes.user_id', '=', $user->id);
        })->whereNull('user_mutes.id');

        // exclude blocked users
        $query->leftJoin('user_blocks AS blocked_users', function($join) use ($user) {
            $join->on('blocked_users.blocked_id', '=', 'users.id')
                 ->where('blocked_users.user_id', '=', $user->id);
        })->whereNull('blocked_users.id');

        // exclude blocked by users
        $query->leftJoin('user_blocks AS blocked_by_users', function($join) use ($user) {
            $join->on('blocked_by_users.user_id', '=', 'users.id')
                 ->where('blocked_by_users.blocked_id', '=', $user->id);
        })->whereNull('blocked_by_users.id');

        // left join follows to check if user follows the status author (checked in the where clause)
        $query->leftJoin('follows', function($join) use ($user) {
            $join->on('follows.follow_id', '=', 'users.id')
                 ->where('follows.user_id', '=', $user->id);
        });

        // only show statuses user is allowed to see
        $query->where(function(Builder $query) use ($user) {
            //Visibility checks: One of the following options must be true

            //Option 1: User is public AND status is public
            $query->where(function(Builder $query) {
                $query->where('users.private_profile', 0)
                      ->whereIn('visibility', [
                          StatusVisibility::PUBLIC->value,
                          StatusVisibility::AUTHENTICATED->value
                      ]);
            });

            //Option 2: Status is from oneself
            $query->orWhere('users.id', $user->id);

            //Option 3: Status is from a followed BUT not unlisted or private
            $query->orWhere(function(Builder $query) {
                // see join above
                $query->where('follows.id', '!=', null)
                      ->whereNotIn('statuses.visibility', [
                          StatusVisibility::UNLISTED->value,
                          StatusVisibility::PRIVATE->value,
                      ]);
            });
        });

        // don't show statuses longer than 20 minutes in the future (to avoid unnecessary clutter)
        $query->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20));

        return $query->select('statuses.*')
                     ->orderByDesc('origin_stopover.departure_real')
                     ->orderByDesc('origin_stopover.departure_planned')
                     ->simplePaginate(15);
    }
}
