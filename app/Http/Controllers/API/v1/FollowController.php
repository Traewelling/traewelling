<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\IdenticalModelException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\User\FollowController as FollowBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class FollowController extends ResponseController
{

    public static function createFollow(Request $request, FollowController $instance): JsonResponse {
        $validated    = $request->validate(['userId' => ['required', 'exists:users,id']]);
        $userToFollow = User::find($validated['userId']);

        try {
            $createFollowResponse = UserBackend::createOrRequestFollow(Auth::user(), $userToFollow);
        } catch (AlreadyFollowingException) {
            return $instance->sendv1Error(['message' => __('controller.user.follow-error')], 409);
        } catch (IdenticalModelException) {
            abort(409);
        }

        return $instance->sendv1Response(new UserResource($createFollowResponse), 201);
    }

    public static function destroyFollow(Request $request, FollowController $instance): JsonResponse {
        $validated      = $request->validate(['userId' => ['required', 'exists:users,id']]);
        $userToUnfollow = User::find($validated['userId']);

        $destroyFollowResponse = UserBackend::destroyFollow(Auth::user(), $userToUnfollow);
        if ($destroyFollowResponse === false) {
            return $instance->sendv1Error(['message' => __('controller.user.follow-404')], 409);
        }

        $userToUnfollow->fresh();
        return $instance->sendv1Response(new UserResource($userToUnfollow));

    }

    public function getFollowers(): AnonymousResourceCollection {
        $followersResponse = FollowBackend::getFollowers(user: auth()->user());
        return UserResource::collection($followersResponse);
    }

    public function getFollowRequests(): AnonymousResourceCollection {
        $followRequestResponse = FollowBackend::getFollowRequests(user: auth()->user());
        return UserResource::collection($followRequestResponse);
    }

    public function getFollowings(): AnonymousResourceCollection {
        $followingResponse = FollowBackend::getFollowings(user: auth()->user());
        return UserResource::collection($followingResponse);
    }
}
