<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\User\FollowController as FollowBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\UserResource;
use App\Models\Follow;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class FollowController extends Controller
{
    /**
     * @OA\Post(
     *      path="/user/{id}/follow",
     *      operationId="createFollow",
     *      tags={"User/Follow"},
     *      summary="Follow a user",
     *      @OA\Parameter (
     *          name="id",
     *          in="path",
     *          description="User-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/User")
     *         )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=409, description="Already following"),
     *       @OA\Response(response=403, description="User is blocked"),
     *       security={
     *           {"passport": {"write-follows"}}, {"token": {}}
     *       }
     *     )
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function createFollow(int $userId): JsonResponse {
        try {
            $userToFollow         = User::findOrFail($userId);
            $createFollowResponse = FollowBackend::createOrRequestFollow(Auth::user(), $userToFollow);
            return $this->sendResponse(new UserResource($createFollowResponse), 201);
        } catch (ModelNotFoundException) {
            return $this->sendError(['message' => 'User not found'], 404);
        } catch (AlreadyFollowingException) {
            return $this->sendError(['message' => __('controller.user.follow-error')], 409);
        } catch (InvalidArgumentException) {
            return $this->sendError(null, 400);
        } catch (AuthorizationException) {
            return $this->sendError(__('profile.youre-blocked-text'), 403);
        }
    }

    /**
     * @OA\Delete(
     *      path="/user/{id}/follow",
     *      operationId="destroyFollow",
     *      tags={"User/Follow"},
     *      summary="Unfollow a user",
     *      @OA\Parameter (
     *          name="id",
     *          in="path",
     *          description="User-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/User")
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="User not found"),
     *       @OA\Response(response=409, description="Already following"),
     *       security={
     *           {"passport": {"write-follows"}}, {"token": {}}
     *       }
     *     )
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function destroyFollow(int $userId): JsonResponse {
        try {
            $userToUnfollow        = User::findOrFail($userId);
            $destroyFollowResponse = UserBackend::destroyFollow(Auth::user(), $userToUnfollow);
            if ($destroyFollowResponse === false) {
                return $this->sendError(['message' => __('controller.user.follow-404')], 409);
            }
            $userToUnfollow->fresh();
            return $this->sendResponse();
        } catch (ModelNotFoundException) {
            return $this->sendError(['message' => 'User not found'], 404);
        } catch (InvalidArgumentException) {
            return $this->sendError(null, 400);
        }
    }

    /**
     * @OA\Get(
     *      path="/settings/followers",
     *      operationId="getFollowers",
     *      tags={"User/Follow", "Settings"},
     *      summary="List all followers",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/User"
     *                  )
     *              ),
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=409, description="Already following"),
     *       security={
     *           {"passport": {"read-settings-followers"}}, {"token": {}}
     *       }
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function getFollowers(): AnonymousResourceCollection {
        return UserResource::collection(FollowBackend::getFollowers(user: auth()->user()));
    }


    /**
     * @OA\Get(
     *      path="/settings/follow-requests",
     *      operationId="getFollowRequests",
     *      tags={"User/Follow", "Settings"},
     *      summary="List all followers",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/User"
     *                  )
     *              ),
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *       security={
     *           {"passport": {"read-settings-followers"}}, {"token": {}}
     *       }
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function getFollowRequests(): AnonymousResourceCollection {
        return UserResource::collection(FollowBackend::getFollowRequests(user: auth()->user()));
    }

    /**
     * @OA\Get(
     *      path="/settings/followings",
     *      operationId="getFollowings",
     *      tags={"User/Follow", "Settings"},
     *      summary="List all users the current user is following",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/User"
     *                  )
     *              ),
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *       security={
     *           {"passport": {"read-settings-followers"}}, {"token": {}}
     *       }
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function getFollowings(): AnonymousResourceCollection {
        return UserResource::collection(FollowBackend::getFollowings(user: auth()->user()));
    }

    /**
     * @OA\Delete(
     *      path="/user/removeFollower",
     *      operationId="removeFollower",
     *      tags={"User/Follow"},
     *      summary="Remove a follower",
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
     *          response=200,
     *          description="successful operation",
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=403, description="Permission denied"),
     *       @OA\Response(response=404, description="Follow not found"),
     *       @OA\Response(response=500, description="Unknown error"),
     *       security={
     *           {"passport": {"write-followers"}}, {"token": {}}
     *       }
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeFollower(Request $request): JsonResponse {
        $validated = $request->validate(['userId' => ['required',]]);
        try {
            $follow = Follow::where('user_id', $validated['userId'])
                            ->where('follow_id', auth()->user()->id)
                            ->firstOrFail();

            $removeResponse = FollowBackend::removeFollower(follow: $follow, user: auth()->user());
            if ($removeResponse === true) {
                return $this->sendResponse();
            }
            Log::error('APIv1/removeFollower: Could not remove follower', ['follow' => $follow, 'user' => auth()->user()]);
            return $this->sendError('Unknown error', 500);
        } catch (ModelNotFoundException) {
            return $this->sendError('Follow not found');
        } catch (PermissionException) {
            return $this->sendError('Permission denied', 403);
        }
    }

    /**
     * @OA\Put(
     *     path="/user/acceptFollowRequest",
     *     operationId="acceptFollowRequest",
     *     tags={"User/Follow"},
     *     summary="Accept a follow request",
     *     @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *     @OA\Property(
     *     property="userId",
     *     title="userId",
     *     format="int64",
     *     description="ID of the user who sent the follow request",
     *     example=1
     *     )
     *    )
     *  ),
     *     @OA\Response(
     *     response=200,
     *     description="successful operation",
     *    ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Request not found"),
     *     @OA\Response(response=500, description="Unknown error"),
     *       security={
     *           {"passport": {"write-followers"}}, {"token": {}}
     *
     *       }
     *     )
     *
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function approveFollowRequest(Request $request): JsonResponse {
        $validated = $request->validate(['userId' => ['required',]]);

        try {
            FollowBackend::approveFollower(auth()->user()->id, $validated['userId']);
            return $this->sendResponse();
        } catch (ModelNotFoundException) {
            return $this->sendError('Request not found');
        } catch (Exception) {
            Log::error('APIv1/approveFollowRequest: Could not approve follow request', ['user' => auth()->user(), 'userId' => $validated['userId']]);
            return $this->sendError('Unknown error', 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/user/rejectFollowRequest",
     *      operationId="rejectFollowRequest",
     *      tags={"User/Follow"},
     *      summary="Reject a follow request",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  title="userId",
     *                  format="int64",
     *                  description="ID of the user who sent the follow request",
     *                  example=1
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=403, description="Permission denied"),
     *       @OA\Response(response=404, description="Request not found"),
     *       security={
     *           {"passport": {"write-followers"}}, {"token": {}}
     *
     *       }
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function rejectFollowRequest(Request $request): JsonResponse {
        $validated = $request->validate(['userId' => ['required',]]);
        try {
            FollowBackend::rejectFollower(auth()->user()->id, $validated['userId']);
            return $this->sendResponse();
        } catch (ModelNotFoundException) {
            return $this->sendError('Request not found');
        } catch (Exception) {
            Log::error('APIv1/rejectFollowRequest: Could not reject follow request', ['user' => auth()->user(), 'userId' => $validated['userId']]);
            return $this->sendError('Unknown error', 500);
        }
    }
}
