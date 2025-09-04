<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Backend\Transport\StatusController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

abstract class DashboardController extends Controller {

    public static function getPrivateDashboard(User $user): Paginator {
        $cutoff = Carbon::now()->addMinutes(20);

        return Status::query()
                     ->with([
                                'event',
                                'likes',
                                'user.blockedByUsers',
                                'user.blockedUsers',
                                'checkin',
                                'tags',
                                'mentions.mentioned',
                                'checkin.originStopover.station.names',
                                'checkin.destinationStopover.station.names',
                                'checkin.trip.stopovers.station.names',
                            ])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->join('users', 'users.id', '=', 'statuses.user_id')
                     ->where(function (Builder $outer) use ($user, $cutoff) {
                         $outer->where(function (Builder $q) use ($user, $cutoff) {
                             $q->where('train_checkins.departure', '<', $cutoff)
                               ->where(StatusController::filterStatusVisibility($user))
                               ->where(StatusController::filterAudienceRestrictions($user));
                         })
                               ->orWhere(function (Builder $q) use ($user, $cutoff) {
                                 $q->where('statuses.user_id', $user->id)
                                   ->where('train_checkins.departure', '<', $cutoff);
                             });
                     })
                     ->orderByDesc('train_checkins.departure')
                     ->select('statuses.*')
                     ->simplePaginate(15);
    }
}
