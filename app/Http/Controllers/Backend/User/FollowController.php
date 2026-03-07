<?php

namespace App\Http\Controllers\Backend\User;

use App\Exceptions\AlreadyFollowingException;
use App\Helpers\CacheKey;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\User;
use App\Notifications\FollowRequestIssued;
use App\Notifications\UserFollowed;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

abstract class FollowController extends Controller
{
    public static function getFollowers(User $user): Paginator
    {
        return $user->userFollowers()->simplePaginate(perPage: 15);
    }

    public static function getFollowRequests(User $user): Paginator
    {
        return $user->userFollowRequests()->simplePaginate(perPage: 15);
    }

    public static function getFollowings(User $user): Paginator
    {
        return $user->userFollowings()->simplePaginate(perPage: 15);
    }

    /**
     * @param  User  $user  - The acting user
     *
     * @throws AuthorizationException
     */
    public static function removeFollower(Follow $follow, User $user): ?bool
    {
        Gate::forUser($user)->authorize('delete', $follow);

        return $follow->delete();
    }

    public static function rejectFollower(User $user, User $follower): void
    {
        FollowRequest::where('user_id', $follower->id)->where('follow_id', $user->id)->firstOrFail()->delete();
    }

    /**
     * @param  User  $user  The user who is approving the follow request
     * @param  User  $approver  The user whose follow request is being approved
     *
     * @throws ModelNotFoundException
     * @throws AuthorizationException
     */
    public static function approveFollower(User $user, User $approver): bool
    {
        $request = FollowRequest::where('user_id', $approver->id)->where('follow_id', $user->id)->firstOrFail();

        try {
            $follow = UserController::createFollow($request->user, $request->requestedFollow, true);
        } catch (AlreadyFollowingException $e) {
            $follow = true;
        }

        if ($follow) {
            $request->delete();
        }

        return $follow;
    }

    /**
     * @throws AlreadyFollowingException
     * @throws InvalidArgumentException
     * @throws AuthorizationException
     *
     * @api v1
     */
    public static function createOrRequestFollow(User $user, User $userToFollow): User
    {
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
            $followRequest = FollowRequest::create([
                'user_id' => $user->id,
                'follow_id' => $userToFollow->id,
            ]);

            $userToFollow->notify(new FollowRequestIssued($followRequest));
            $userToFollow->refresh();
            $user->refresh();

            return $userToFollow;
        }

        $follow = Follow::create([
            'user_id' => $user->id,
            'follow_id' => $userToFollow->id,
        ]);
        $userToFollow->refresh();
        $userToFollow->notify(new UserFollowed($follow));
        Cache::forget(CacheKey::getFriendsLeaderboardKey($user->id));

        return $userToFollow;
    }

    public static function isFollowingEachOther(User $user, User $otherUser): bool
    {
        return $user->userFollowers->contains('id', $otherUser->id)
               && $user->userFollowings->contains('id', $otherUser->id);
    }
}
