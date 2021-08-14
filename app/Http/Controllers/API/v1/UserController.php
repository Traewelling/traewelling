<?php

namespace App\Http\Controllers\API\v1;


use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\IdentidalModelException;
use App\Exceptions\PermissionException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

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
        } catch (IdentidalModelException) {
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
}
