<?php

namespace App\Http\Controllers\API\v1;


use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\IdenticalModelException;
use App\Exceptions\PermissionException;
use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends ResponseController
{
    public function authenticated(): UserResource {
        return new UserResource(Auth::user());
    }

    /**
     * Returns Model of user
     * @param string $username
     * @return UserResource
     * @todo Maybe put this into another method?
     */
    public function show(string $username): UserResource {
        return new UserResource(User::where('username', 'like', $username)->firstOrFail());
    }

    /**
     * Returns paginated statuses for user
     * @param string $username
     * @return AnonymousResourceCollection
     */
    public static function statuses(string $username): AnonymousResourceCollection {
        $user = User::where('username', 'like', $username)->firstOrFail();
        try {
            $userResponse = UserBackend::statusesForUser($user);
        } catch (PermissionException) {
            abort(404, "No statuses found, or statuses are not visible to you.");
        }
        return StatusResource::collection($userResponse);
    }

    public function createFollow(Request $request): JsonResponse {
        $validated    = $request->validate(['userId' => ['required', 'exists:users,id']]);
        $userToFollow = User::find($validated['userId']);

        try {
            $createFollowResponse = UserBackend::createOrRequestFollow(Auth::user(), $userToFollow);
        } catch (AlreadyFollowingException) {
            return $this->sendError(['message' => __('controller.user.follow-error')], 409);
        } catch (IdenticalModelException) {
            abort(409);
        }

        return $this->sendv1Response(new UserResource($createFollowResponse), 201);
    }

    public function destroyFollow(Request $request): JsonResponse {
        $validated      = $request->validate(['userId' => ['required', 'exists:users,id']]);
        $userToUnfollow = User::find($validated['userId']);

        $destroyFollowResponse = UserBackend::destroyFollow(Auth::user(), $userToUnfollow);
        if ($destroyFollowResponse === false) {
            return $this->sendError(['message' => __('controller.user.follow-404')], 409);
        }

        $userToUnfollow->fresh();
        return $this->sendv1Response(new UserResource($userToUnfollow));

    }

    public function createMute(Request $request): JsonResponse {
        $validated     = $request->validate([
                                                'userId' => [
                                                    'required',
                                                    'exists:users,id',
                                                    Rule::notIn([auth()->user()->id]),
                                                ]
                                            ]);
        $userToBeMuted = User::find($validated['userId']);

        try {
            $muteUserResponse = \App\Http\Controllers\Backend\UserController::muteUser(auth()->user(), $userToBeMuted);
        } catch (UserAlreadyMutedException) {
            return $this->sendError(['message' => __('user.already-muted', ['username' => $userToBeMuted->username])], 409);
        }

        $userToBeMuted->refresh();
        if ($muteUserResponse) {
            return $this->sendv1Response(new UserResource($userToBeMuted), 201);
        }
        return $this->sendError(['message' => __('messages.exception.general')], 400);
    }

    public function destroyMute(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'userId' => [
                                                'required',
                                                'exists:users,id',
                                            ]
                                        ]);

        $userToBeUnmuted = User::find($validated['userId']);

        try {
            $unmuteUserResponse = \App\Http\Controllers\Backend\UserController::unmuteUser(auth()->user(), $userToBeUnmuted);

        } catch (UserNotMutedException) {
            return $this->sendError(['message' => __('user.already-unmuted', ['username' => $userToBeUnmuted->username])], 409);
        }

        $userToBeUnmuted->refresh();
        if ($unmuteUserResponse) {
            return $this->sendv1Response(new UserResource($userToBeUnmuted));
        }
        return $this->sendError(['message' => __('messages.exception.general')], 400);
    }
}
