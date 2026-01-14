<?php

namespace App\Http\Controllers\Backend\User;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

abstract class DashboardController extends Controller
{
    public static function getPrivateDashboard(User $user): Paginator
    {
        $followingIDs = $user->follows->pluck('id');
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
            'checkin.trip.stopovers.station',
        ])
            ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
            ->select('statuses.*')
            ->where('train_checkins.departure', '<', now()->addMinutes(20))
            ->where('statuses.created_at', '>=', now()->subDays(7))
            ->whereIn('statuses.user_id', $followingIDs)
            ->whereNotIn('statuses.user_id', $user->mutedUsers->pluck('id'))
            ->where(function (EloquentBuilder $query) use ($user) {
                $query->whereIn('statuses.visibility', [
                    StatusVisibility::PUBLIC->value,
                    StatusVisibility::FOLLOWERS->value,
                    StatusVisibility::AUTHENTICATED->value,
                ])
                    ->orWhere(function (EloquentBuilder $trustedQuery) use ($user) {
                        $trustedQuery->where('statuses.visibility', StatusVisibility::TRUSTED->value)
                            ->whereExists(function (QueryBuilder $sub) use ($user) {
                                $sub->from('trusted_users')
                                    ->whereColumn('trusted_users.user_id', 'statuses.user_id')
                                    ->where('trusted_users.trusted_id', $user->id)
                                    ->where(function ($expireQuery) {
                                        $expireQuery->whereNull('trusted_users.expires_at')
                                            ->orWhere('trusted_users.expires_at', '>', now());
                                    });
                            });
                    });
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('statuses.user_id', $user->id)
                    ->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20));
            })
            ->orderBy('train_checkins.departure', 'desc')
            ->latest()
            ->simplePaginate(15);
    }
}
