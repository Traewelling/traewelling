<?php

namespace App\Http\Controllers\Backend\User;

use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class FollowController extends Controller
{
    public static function getFollowers(User $user): Paginator {
        return $user->userFollowers()->simplePaginate(perPage: 15);
    }

    public static function getFollowRequests(User $user): Paginator {
        return $user->userFollowRequests()->simplePaginate(perPage: 15);
    }

    public static function getFollowings(User $user): Paginator {
        return $user->userFollowings()->simplePaginate(perPage: 15);
    }

    /**
     * @param Follow $follow
     * @param User   $user - The acting user
     *
     * @return bool|null
     * @throws PermissionException
     */
    public static function removeFollower(Follow $follow, User $user): bool|null {
        if ($user->cannot('delete', $follow)) {
            throw new PermissionException();
        }
        return $follow->delete();
    }

    /**
     * @param int $userId
     * @param int $followerID
     *
     * @return FollowRequest|null
     */
    public static function rejectFollower(int $userId, int $followerID): ?FollowRequest {
        $request = FollowRequest::where('user_id', $followerID)->where('follow_id', $userId)->firstOrFail();

        $request->delete();
        return $request;
    }

    /**
     *
     * @param int $userId     The id of the user who is approving a follower
     * @param int $approverId The id of a to-be-approved follower
     *
     * @throws ModelNotFoundException|AlreadyFollowingException
     */
    public static function approveFollower(int $userId, int $approverId): bool {
        $request = FollowRequest::where('user_id', $approverId)->where('follow_id', $userId)->firstOrFail();

        $follow = UserController::createFollow($request->user, $request->requestedFollow, true);

        if ($follow) {
            $request->delete();
        }
        return $follow;
    }

}
