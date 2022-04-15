<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\PermissionException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\User\FollowController as FollowBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\UserResource;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class FollowController extends ResponseController
{

    public static function createFollow(Request $request, FollowController $instance): JsonResponse {
        $validated    = $request->validate(['userId' => ['required', 'exists:users,id']]);
        $userToFollow = User::find($validated['userId']);

        try {
            $createFollowResponse = UserBackend::createOrRequestFollow(Auth::user(), $userToFollow);
        } catch (AlreadyFollowingException) {
            return $instance->sendv1Error(['message' => __('controller.user.follow-error')], 409);
        } catch (InvalidArgumentException) {
            abort(409);
        }

        return $instance->sendv1Response(new UserResource($createFollowResponse), 204);
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

    public function removeFollower(Request $request): void {
        $validated = $request->validate([
                                            'userId' => [
                                                'required',
                                                Rule::in(auth()->user()->followers->pluck('user_id')),
                                            ]
                                        ]);

        $follow = Follow::where('user_id', $validated['userId'])
                        ->where('follow_id', auth()->user()->id)
                        ->firstOrFail();

        try {
            $removeResponse = FollowBackend::removeFollower(follow: $follow, user: auth()->user());
        } catch (PermissionException) {
            abort(403);
        }

        if ($removeResponse === true) {
            abort(204);
        }
        abort(500);
    }

    public function approveFollowRequest(Request $request) {
        $validated = $request->validate([
                                            'userId' => [
                                                'required',
                                                Rule::in(auth()->user()->followRequests->pluck('user_id'))
                                            ]
                                        ]);

        try {
            FollowBackend::approveFollower(auth()->user()->id, $validated['userId']);
            abort(204);
        } catch (ModelNotFoundException) {
            abort(404);
        } catch (AlreadyFollowingException $exception) {
            report($exception);
        }
        abort(500);
    }

    public function rejectFollowRequest(Request $request) {
        $validated = $request->validate([
                                            'userId' => [
                                                'required',
                                                Rule::in(auth()->user()->followRequests->pluck('user_id'))
                                            ]
                                        ]);
        try {
            FollowBackend::rejectFollower(auth()->user()->id, $validated['userId']);
            abort(204);
        } catch (ModelNotFoundException) {
            abort(404);
        }
        abort(500);
    }
}
