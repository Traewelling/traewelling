<?php

namespace App\Http\Controllers;

use App\Enum\CacheKey;
use App\Enum\StatusVisibility;
use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\SettingsController as BackendSettingsController;
use App\Http\Controllers\Backend\User\BlockController;
use App\Http\Controllers\Backend\User\SessionController;
use App\Http\Controllers\Backend\User\TokenController;
use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\User;
use App\Notifications\FollowRequestApproved;
use App\Notifications\FollowRequestIssued;
use App\Notifications\UserFollowed;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use Mastodon;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class UserController extends Controller
{

    #[ArrayShape(['status' => "string"])]
    public static function updateProfilePicture($avatar): array {
        BackendSettingsController::updateProfilePicture($avatar);
        return ['status' => ':ok'];
    }

    public static function getProfilePage(string $username): ?array {
        $user = User::where('username', 'like', $username)->first();
        if ($user === null) {
            return null;
        }
        try {
            $statuses = self::statusesForUser($user);
        } catch (AuthorizationException) {
            $statuses = null;
        }

        return [
            'username'    => $username,
            'statuses'    => $statuses,
            'twitterUrl'  => $user->twitterUrl,
            'mastodonUrl' => $user->mastodonUrl,
            'user'        => $user
        ];
    }

    /**
     * @param User     $user
     * @param int|null $limit
     *
     * @return LengthAwarePaginator|null
     * @api v1
     * @frontend
     */
    public static function statusesForUser(User $user, int $limit = null): ?LengthAwarePaginator {
        Gate::authorize('view', $user);
        return $user->statuses()
                    ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                    ->with([
                               'user', 'likes', 'trainCheckin.Origin', 'trainCheckin.Destination',
                               'trainCheckin.HafasTrip.stopoversNEW', 'event'
                           ])
                    ->where(function($query) {
                        $user = Auth::check() ? auth()->user()->id : null;
                        $query->whereIn('statuses.visibility', [
                            StatusVisibility::PUBLIC->value,
                            StatusVisibility::UNLISTED->value,
                        ])
                              ->orWhere('statuses.user_id', $user)
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
                    ->paginate($limit !== null && $limit <= 15 ? $limit : 15);
    }

    /**
     * @param User $user
     * @param User $userToFollow
     *
     * @return User
     * @throws AlreadyFollowingException
     * @throws InvalidArgumentException
     * @throws AuthorizationException
     * @api v1
     */
    public static function createOrRequestFollow(User $user, User $userToFollow): User {
        if ($user->is($userToFollow)) {
            throw new InvalidArgumentException();
        }
        if ($user->follows->contains('id', $userToFollow->id) || $userToFollow->followRequests->contains('user_id', $user->id)) {
            throw new AlreadyFollowingException($user, $userToFollow);
        }
        if (BlockController::isBlocked($user, $userToFollow) || BlockController::isBlocked($userToFollow, $user)) {
            throw new AuthorizationException();
        }

        // Request follow if user is a private profile
        if ($userToFollow->private_profile) {
            $follow = FollowRequest::create([
                                                'user_id'   => $user->id,
                                                'follow_id' => $userToFollow->id
                                            ]);

            $userToFollow->notify(new FollowRequestIssued($follow));
            $userToFollow->refresh();
            $user->refresh();
            return $userToFollow;
        }

        Follow::create([
                           'user_id'   => $user->id,
                           'follow_id' => $userToFollow->id
                       ]);
        $userToFollow->fresh();
        Cache::forget(CacheKey::getFriendsLeaderboardKey($user->id));
        return $userToFollow;
    }

    //Save Changes on Settings-Page

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
     * @deprecated
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
        if (self::isFollowing($user, $userToFollow)) {
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
        return self::isFollowing($user, $userToFollow);
    }

    /**
     * Returnes whether $user follows $userFollow
     *
     * @param User $user
     * @param User $userFollow
     *
     * @return bool
     * @deprecated Following-Attribute
     */
    private static function isFollowing(User $user, User $userFollow): bool {
        return $user->follows->contains('id', $userFollow->id);
    }

    /**
     * Add $userToFollow to $user's FollowerRequests
     *
     * @param User $user
     * @param User $userToFollow The user of the person who is followed
     *
     * @return bool
     * @throws AlreadyFollowingException
     * @deprecated
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
     */
    public static function destroyFollow(User $user, User $userToUnfollow): bool {
        if (!self::isFollowing($user, $userToUnfollow)) {
            return false;
        }
        Follow::where('user_id', $user->id)->where('follow_id', $userToUnfollow->id)->delete();
        $user->load('follows');
        return self::isFollowing($user, $userToUnfollow) == false;
    }

    public static function registerByDay(Carbon $date): int {
        return User::where("created_at", ">=", $date->copy()->startOfDay())
                   ->where("created_at", "<=", $date->copy()->endOfDay())
                   ->count();
    }

    /**
     * @param string|null $searchQuery
     *
     * @return Paginator
     * @deprecated is now in backend/usercontroller for api v1
     */
    public static function searchUser(?string $searchQuery): Paginator {
        $validator = Validator::make(
            ['searchQuery' => $searchQuery],
            ['searchQuery' => ['required', 'regex:/^[äöüÄÖÜa-zA-Z0-9_\-]+$/', 'max:50']]
        );
        if ($validator->fails()) {
            abort(400);
        }
        $escapedQuery = str_replace('_', "\_", $searchQuery);
        return User::join('train_checkins', 'train_checkins.user_id', '=', 'users.id')
                   ->groupBy(['users.id', 'users.username', 'users.name'])
                   ->select(['users.id', 'users.username', 'users.name'])
                   ->orderByDesc(DB::raw('MAX(train_checkins.created_at)'))
                   ->where('name', 'like', '%' . $escapedQuery . '%')
                   ->orWhere('username', 'like', '%' . $escapedQuery . '%')
                   ->simplePaginate(10);
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
            return redirect()->route('settings')->with('alert-success', __('settings.revoke-token.success'));
        } catch (PermissionException) {
            return redirect()->route('settings')->withErrors(__('messages.exception.general'));
        }
    }

    public function SaveAccount(Request $request): RedirectResponse {

        $this->validate($request, [
            'name' => 'required|max:120'
        ]);
        $user       = User::where('id', Auth::user()->id)->first();
        $user->name = $request['name'];
        $user->update();
        return redirect()->route('account');
    }
}
