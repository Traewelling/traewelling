<?php

namespace App\Http\Controllers;

use App\Enum\StatusVisibility;
use App\Exceptions\AlreadyFollowingException;
use App\Http\Controllers\Backend\User\BlockController;
use App\Http\Controllers\Backend\User\SessionController;
use App\Http\Controllers\Backend\User\TokenController;
use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\User;
use App\Notifications\FollowRequestApproved;
use App\Notifications\FollowRequestIssued;
use App\Notifications\UserFollowed;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class UserController extends Controller
{

    /**
     * @param User     $user
     * @param int|null $limit
     *
     * @return Paginator|null
     * @api v1
     * @frontend
     */
    public static function statusesForUser(User $user, int $limit = null): ?Paginator {
        Gate::authorize('view', $user);
        return $user->statuses()
                    ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                    ->with([
                               'event',
                               'likes',
                               'user.blockedByUsers',
                               'user.blockedUsers',
                               'checkin',
                               'tags',
                               'checkin.originStopover.station.names',
                               'checkin.destinationStopover.station.names',
                               'checkin.trip.stopovers.station.names',
                           ])
                    ->where(function($query) {
                        $query->whereIn('statuses.visibility', [
                            StatusVisibility::PUBLIC->value,
                            StatusVisibility::UNLISTED->value,
                        ])
                              ->orWhere('statuses.user_id', Auth::check() ? auth()->user()->id : null)
                              ->orWhere(function($query) {
                                  $followings = Auth::check() ? auth()->user()->follows()->select('follow_id') : [];
                                  $query->where('statuses.visibility', StatusVisibility::FOLLOWERS->value)
                                        ->whereIn('statuses.user_id', $followings);
                                  if (Auth::check()) {
                                      $query->orWhere(function($query) {
                                          $query->where('statuses.visibility', StatusVisibility::AUTHENTICATED->value);
                                      });
                                  }
                              });
                    })
                    ->select('statuses.*')
                    ->orderByDesc('train_checkins.departure')
                    ->simplePaginate($limit !== null && $limit <= 15 ? $limit : 15);
    }

    /**
     * Add $userToFollow to $user's Followings
     *
     * @param User $user
     * @param User $userToFollow
     * @param bool $isApprovedRequest
     *
     * @return bool
     * @throws AlreadyFollowingException
     * @throws AuthorizationException
     * @deprecated @todo replace frontend by api endpoint
     */
    public static function createFollow(User $user, User $userToFollow, bool $isApprovedRequest = false): bool {
        if ($user->is($userToFollow)) {
            return false;
        }
        if (BlockController::isBlocked($user, $userToFollow) || BlockController::isBlocked($userToFollow, $user)) {
            throw new AuthorizationException();
        }

        //disallow re-following, if you already follow them
        //Also disallow following, if user is a private profile
        if ($user->follows->contains('id', $userToFollow->id)) {
            throw new AlreadyFollowingException($user, $userToFollow);
        }
        // Request follow if user is a private profile
        if ($userToFollow->private_profile && !$isApprovedRequest) {
            return self::requestFollow($user, $userToFollow);
        }

        $follow = Follow::create([
                                     'user_id'   => $user->id,
                                     'follow_id' => $userToFollow->id
                                 ]);
        if (!$isApprovedRequest) {
            $userToFollow->notify(new UserFollowed($follow));
        } else {
            $user->notify(new FollowRequestApproved($follow));
        }
        $user->load('follows');
        return $user->follows->contains('id', $userToFollow->id);
    }

    /**
     * Add $userToFollow to $user's FollowerRequests
     *
     * @param User $user
     * @param User $userToFollow The user of the person who is followed
     *
     * @return bool
     * @throws AlreadyFollowingException
     * @deprecated @todo replace frontend by api endpoint
     */
    public static function requestFollow(User $user, User $userToFollow): bool {
        if ($userToFollow->followRequests->contains('user_id', $user->id)) {
            throw new AlreadyFollowingException($user, $userToFollow);
        }
        $follow = FollowRequest::create([
                                            'user_id'   => $user->id,
                                            'follow_id' => $userToFollow->id
                                        ]);

        $userToFollow->notify(new FollowRequestIssued($follow));
        $userToFollow->load('followRequests');
        return $userToFollow->followRequests->contains('user_id', $user->id);
    }

    /**
     * Remove $userToUnfollow from $user's Follower
     *
     * @param User $user
     * @param User $userToUnfollow The user of the person who was followed and now isn't
     *
     * @return bool
     * @deprecated @todo replace frontend by api endpoint
     */
    public static function destroyFollow(User $user, User $userToUnfollow): bool {
        if (!$user->follows->contains('id', $userToUnfollow->id)) {
            return false;
        }
        Follow::where('user_id', $user->id)->where('follow_id', $userToUnfollow->id)->delete();
        $user->load('follows');
        return !$user->follows->contains('id', $userToUnfollow->id);
    }

    public function deleteSession(): RedirectResponse {
        $user = Auth::user();
        Auth::logout();
        SessionController::deleteAllSessionsFor(user: $user);
        return redirect()->route('static.welcome');
    }

    /**
     * delete a specific session for user
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteToken(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'tokenId' => ['required', 'exists:oauth_access_tokens,id']
                                        ]);

        try {
            TokenController::revokeToken(tokenId: $validated['tokenId'], user: auth()->user());
            return redirect()->route('settings.tokens')->with('alert-success', __('settings.revoke-token.success'));
        } catch (AuthorizationException) {
            return redirect()->route('settings.tokens')->withErrors(__('messages.exception.general'));
        }
    }
}
