<?php

namespace App\Http\Controllers\Backend\User;

use App\Enum\StatusVisibility;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;

abstract class DashboardController extends Controller
{
    public static function getGlobalDashboard(): Paginator {
        return Status::with([
                                'event', 'likes', 'user', 'trainCheckin',
                                'trainCheckin.Origin', 'trainCheckin.Destination',
                                'trainCheckin.HafasTrip.stopoversNEW.trainStation'
                            ])
                     ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                     ->join('users', 'statuses.user_id', '=', 'users.id')
                     ->where(function(Builder $query) {
                         //Visibility checks: One of the following options must be true

                         //Option 1: User is public AND status is public
                         $query->where(function(Builder $query) {
                             $query->where('users.private_profile', 0)
                                   ->where('visibility', StatusVisibility::PUBLIC);
                         });

                         //Option 2: Status is from oneself
                         if (auth()->check()) {
                             $query->orWhere('users.id', auth()->user()->id);
                         }

                         //Option 3: Status is from a followed BUT not unlisted or private
                         $query->orWhere(function(Builder $query) {
                             $followings = Auth::check() ? auth()->user()->follows()->select('follow_id') : [];
                             $query->whereIn('users.id', $followings)
                                   ->whereNotIn('visibility', [StatusVisibility::UNLISTED, StatusVisibility::PRIVATE]);
                         });
                     })
                     ->where('train_checkins.departure', '<', Carbon::now()->addMinutes(20)->toIso8601String())
                     ->whereNotIn('statuses.user_id', auth()->user()->mutedUsers()->select('muted_id'))
                     ->select('statuses.*')
                     ->orderBy('train_checkins.departure', 'desc')
                     ->withCount('likes')
                     ->simplePaginate(15);
    }
}
