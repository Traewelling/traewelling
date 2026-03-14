<?php

namespace App\Http\Controllers\Backend\User;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

abstract class DashboardController extends Controller
{
    public static function getPrivateDashboard(User $user): Paginator
    {
        $followingIDs = $user->follows->pluck('id');
        $followingIDs[] = $user->id;

        $since = now()->subDays(7)->format('Y-m-d H:i:s');
        $departureLimit = now()->addMinutes(20)->format('Y-m-d H:i:s');
        $mutedIds = $user->mutedUsers->pluck('id');

        return Status::with([
            'event',
            'likes',
            'user.blockedByUsers',
            'user.blockedUsers',
            'createdByUser',
            'checkin',
            'tags',
            'mentions.mentioned',
            'checkin.originStopover.station',
            'checkin.destinationStopover.station',
            'checkin.trip.stopovers.station',
        ])
            ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
            ->select('statuses.*')
            ->where(function (EloquentBuilder $query) use ($user, $followingIDs, $mutedIds, $since, $departureLimit) {
                // Branch 1: statuses from followed users (incl. self) with visibility filter
                $query->where('train_checkins.departure', '<', $departureLimit)
                    ->where('statuses.created_at', '>=', $since)
                    ->whereIn('statuses.user_id', $followingIDs)
                    ->whereNotIn('statuses.user_id', $mutedIds)
                    ->where(function (EloquentBuilder $visibilityQuery) use ($user) {
                        $visibilityQuery->whereIn('statuses.visibility', [
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
                    });
            })
            ->orWhere(function (EloquentBuilder $query) use ($user, $since, $departureLimit) {
                // Branch 2: own private statuses not covered by the visibility filter above
                $query->where('statuses.user_id', $user->id)
                    ->where('statuses.created_at', '>=', $since)
                    ->where('train_checkins.departure', '<', $departureLimit);
            })
            ->orderByDesc('train_checkins.departure')
            ->simplePaginate(15);
    }
}
