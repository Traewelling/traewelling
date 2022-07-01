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
                                'event', 'likes', 'user', 'trainCheckin',
                                'trainCheckin.Origin', 'trainCheckin.Destination',
                                'trainCheckin.HafasTrip.stopoversNEW.trainStation'
                            ])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->select('statuses.*')
                     ->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20)->toIso8601String())
                     ->orderBy('train_checkins.departure', 'desc')
                     ->whereIn('statuses.user_id', $followingIDs)
                     ->whereIn('visibility', [
                         StatusVisibility::PUBLIC->value,
                         StatusVisibility::FOLLOWERS->value,
                         StatusVisibility::AUTHENTICATED->value
                     ])
                     ->orWhere('statuses.user_id', $user->id)
                     ->withCount('likes')
                     ->latest()
                     ->simplePaginate(15);
    }

    public static function getGlobalDashboard(User $user): Paginator {
        return Status::with([
                                'event', 'likes', 'user', 'trainCheckin',
                                'trainCheckin.Origin', 'trainCheckin.Destination',
                                'trainCheckin.HafasTrip.stopoversNEW.trainStation'
                            ])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->join('users', 'statuses.user_id', '=', 'users.id')
                     ->where(function(Builder $query) use ($user) {
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
                         $query->orWhere(function(Builder $query) use ($user) {
                             $query->whereIn('users.id', $user->follows()->select('follow_id'))
                                   ->whereNotIn('visibility', [
                                       StatusVisibility::UNLISTED->value,
                                       StatusVisibility::PRIVATE->value,
                                   ]);
                         });
                     })
                     ->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20)->toIso8601String())
                     ->whereNotIn('statuses.user_id', $user->mutedUsers()->select('muted_id'))
                     ->select('statuses.*')
                     ->orderBy('train_checkins.departure', 'desc')
                     ->withCount('likes')
                     ->simplePaginate(15);
    }
}
