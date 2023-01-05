<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\User\FollowController as FollowBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\UserResource;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class FollowController extends Controller
{
    /**
     * @OA\Post(
     *      path="/user/createFollow",
     *      operationId="createFollow",
     *      tags={"User"},
     *      summary="Follow a user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  title="userId",
     *                  format="int64",
     *                  description="ID of the to-be-followed user",
     *                  example=1
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=409, description="Already following"),
     *       @OA\Response(response=403, description="User is blocked"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     * @param Request          $request
     * @param FollowController $instance
     *
     * @return JsonResponse
     */
    public static function createFollow(Request $request, FollowController $instance): JsonResponse {
        $validated    = $request->validate(['userId' => ['required', 'exists:users,id']]);
        $userToFollow = User::find($validated['userId']);

        try {
            $createFollowResponse = UserBackend::createOrRequestFollow(Auth::user(), $userToFollow);
            return $instance->sendResponse(new UserResource($createFollowResponse), 201);
        } catch (AlreadyFollowingException) {
            return $instance->sendError(['message' => __('controller.user.follow-error')], 409);
        } catch (InvalidArgumentException) {
            return $instance->sendError(null, 400);
        } catch (AuthorizationException) {
            return $instance->sendError(__('profile.youre-blocked-text'), 403);
        }
    }

    /**
     * @OA\Delete(
     *      path="/user/destroyFollow",
     *      operationId="destroyFollow",
     *      tags={"User"},
     *      summary="Unfollow a user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  title="userId",
     *                  format="int64",
     *                  description="ID of the to-be-unfollowed user",
     *                  example=1
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=409, description="Already following"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     * @param Request          $request
     * @param FollowController $instance
     *
     * @return JsonResponse
     */
    public static function destroyFollow(Request $request, FollowController $instance): JsonResponse {
        $validated      = $request->validate(['userId' => ['required', 'exists:users,id']]);
        $userToUnfollow = User::find($validated['userId']);

        $destroyFollowResponse = UserBackend::destroyFollow(Auth::user(), $userToUnfollow);
        if ($destroyFollowResponse === false) {
            return $instance->sendError(['message' => __('controller.user.follow-404')], 409);
        }

        $userToUnfollow->fresh();
        return $instance->sendResponse(new UserResource($userToUnfollow));
    }

    public function getFollowers(): AnonymousResourceCollection {
        return UserResource::collection(FollowBackend::getFollowers(user: auth()->user()));
    }

    public function getFollowRequests(): AnonymousResourceCollection {
        return UserResource::collection(FollowBackend::getFollowRequests(user: auth()->user()));
    }

    public function getFollowings(): AnonymousResourceCollection {
        return UserResource::collection(FollowBackend::getFollowings(user: auth()->user()));
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
