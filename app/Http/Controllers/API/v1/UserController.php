<?php

namespace App\Http\Controllers\API\v1;


use App\Exceptions\UserAlreadyBlockedException;
use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotBlockedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\Backend\UserController as BackendUserBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class UserController extends Controller
{

    /**
     * @OA\Delete(
     *     path="/settings/account",
     *     operationId="deleteUserAccount",
     *     tags={"Settings"},
     *     summary="Delete User Account",
     *     description="Deletes the Account for the user and all posts created by it",
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property (
     *                  property="confirmation",
     *                  title="confirmation",
     *                  description="Username of the to be deleted account (needs to match the currently logged in
     *                  user)", example="Gertrud123"
     *              )
     *          )
     *     ),
     * @OA\Response(
     *          response=200,
     *          description="successful operation"
     *     ),
     * @OA\Response(response=409, description="Conflict. This should not happen but it tries to prevent a 500."),
     * @OA\Response(response=400, description="Bad request"),
     * @OA\Response(response=401, description="Not logged in"),
     * @OA\Response(response=403, description="User not authorized to do this action"),
     *       security={
     *           {"passport": {"extra-delete"}}, {"token": {}}
     *
     *       }
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAccount(Request $request): JsonResponse {
        $request->validate(['confirmation' => ['required', Rule::in([auth()->user()->username])]]);

        if (!BackendUserBackend::deleteUserAccount(user: auth()->user())) {
            return $this->sendError(__('messages.exception.general'), 500);
        }
        return $this->sendResponse(true);
    }

    /**
     * @OA\Get(
     *      path="/user/{username}/statuses",
     *      operationId="getStatusesForUser",
     *      tags={"User", "Status"},
     *      summary="[Auth optional] Get paginated statuses for single user",
     *      description="Returns paginated statuses of a single user specified by the username",
     *      @OA\Parameter (
     *           name="username",
     *           in="path",
     *           description="username",
     *           example="Gertrud123",
     *      ),
     *      @OA\Parameter (
     *          name="page",
     *          description="Page of pagination",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/StatusResource"
     *                  )
     *              ),
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       security={
     *           {"passport": {}}, {"token": {}}
     *       },
     *       @OA\Response(response=403, description="Forbidden, User is blocked"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     *
     * Returns paginated statuses for user
     *
     * @param Request $request
     * @param string  $username
     *
     * @return AnonymousResourceCollection
     */
    public function statuses(Request $request, string $username): AnonymousResourceCollection {
        $user = User::where('username', 'like', $username)->firstOrFail();

        $validated = $request->validate([
                                            'limit' => ['nullable', 'integer', 'min:1', 'max:15'],
                                        ]);

        try {
            $this->authorize('view', $user);
            $userResponse = UserBackend::statusesForUser(user: $user, limit: $validated['limit'] ?? null);
        } catch (AuthorizationException $exception) {
            abort(403, $exception->response()->message() ?? 'No statuses found, or statuses are not visible to you.');
        }
        return StatusResource::collection($userResponse);
    }

    /**
     * @OA\Get(
     *      path="/user/{username}",
     *      operationId="showUser",
     *      tags={"User"},
     *      summary="[Auth optional] Get information for single user",
     *      description="Returns general information, metadata and statistics for a user",
     *      @OA\Parameter (
     *           name="username",
     *           in="path",
     *           description="username",
     *           example="Gertrud123",
     *      ),
     *      @OA\Parameter (
     *          name="page",
     *          description="Page of pagination",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data",
     *                      ref="#/components/schemas/User"
     *              ),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=403, description="Forbidden, User is blocked"),
     *       @OA\Response(response=404, description="User not found"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     * Returns Model of user
     *
     * @param string $username
     *
     * @return UserResource
     * @todo Maybe put this into another method?
     */
    public function show(string $username): UserResource {
        $user = User::where('username', 'like', $username)->firstOrFail();

        try {
            $this->authorize('view', $user);
        } catch (AuthorizationException $exception) {
            abort(403, $exception->response()->message() ?? 'User not accessible.');
        }

        return new UserResource($user);
    }

    /**
     * @OA\Post(
     *      path="/user/{id}/block",
     *      operationId="createBlock",
     *      tags={"User/Hide and Block"},
     *      summary="Block a user",
     *      description="Block a specific user. That user will not be able to see your statuses or profile information,
     *      and cannot send you follow requests. Public statuses are still visible through the incognito mode.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  title="userId",
     *                  format="int",
     *                  description="ID of the to-be-blocked user",
     *                  example=1
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", ref="#/components/schemas/User")
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Not logged in"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       @OA\Response(response=404, description="User not found"),
     *       @OA\Response(response=409, description="User is already blocked"),
     *       security={
     *           {"passport": {"write-block"}}, {"token": {}}
     *
     *       }
     *     )
     *
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function createBlock(int $userId): JsonResponse {
        try {
            $userToBeBlocked   = User::findOrFail($userId);
            $blockUserResponse = BackendUserBackend::blockUser(auth()->user(), $userToBeBlocked);
            $userToBeBlocked->refresh();
            if ($blockUserResponse) {
                return $this->sendResponse(new UserResource($userToBeBlocked), 201);
            }
            return $this->sendError(['message' => __('messages.exception.general')], 400);
        } catch (ModelNotFoundException) {
            return $this->sendError(['message' => 'User not found'], 404);
        } catch (UserAlreadyBlockedException) {
            return $this->sendError([
                                        'message' => __(
                                            'user.already-blocked',
                                            ['username' => $userToBeBlocked->username]
                                        )
                                    ], 409);
        }
    }

    /**
     * @OA\Delete(
     *      path="/user/{id}/block",
     *      operationId="destroyBlock",
     *      tags={"User/Hide and Block"},
     *      summary="Unmute a user",
     *      description="Unblock a specific user. They are now able to see your statuses and profile information again,
     *      and send you follow requests.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  title="userId",
     *                  format="int",
     *                  description="ID of the to-be-unblocked user",
     *                  example=1
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", ref="#/components/schemas/User")
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Not logged in"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       @OA\Response(response=404, description="User not found"),
     *       @OA\Response(response=409, description="User is not blocked"),
     *       security={
     *           {"passport": {"write-block"}}, {"token": {}}
     *
     *       }
     *     )
     *
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function destroyBlock(int $userId): JsonResponse {
        try {
            $userToBeUnblocked   = User::findOrFail($userId);
            $unblockUserResponse = BackendUserBackend::unblockUser(auth()->user(), $userToBeUnblocked);
            $userToBeUnblocked->refresh();
            if ($unblockUserResponse) {
                return $this->sendResponse(new UserResource($userToBeUnblocked));
            }
            return $this->sendError(['message' => __('messages.exception.general')], 400);
        } catch (ModelNotFoundException) {
            return $this->sendError(['message' => 'User not found'], 404);
        } catch (UserNotBlockedException) {
            return $this->sendError([
                                        'message' => __(
                                            'user.already-unblocked',
                                            ['username' => $userToBeUnblocked->username]
                                        )
                                    ], 409);
        }
    }

    /**
     * @OA\Post(
     *      path="/user/{id}/mute",
     *      operationId="createMute",
     *      tags={"User/Hide and Block"},
     *      summary="Mute a user",
     *      description="Mute a specific user. That way they will not be shown on your dashboard and in the active
     *      journeys tab",
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
     *              @OA\Property(property="data", ref="#/components/schemas/User")
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Not logged in"),
     *       @OA\Response(response=409, description="User is already muted"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={
     *           {"passport": {"write-block"}}, {"token": {}}
     *
     *       }
     *     )
     *
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function createMute(int $userId): JsonResponse {
        try {
            $userToBeMuted    = User::findOrFail($userId);
            $muteUserResponse = BackendUserBackend::muteUser(auth()->user(), $userToBeMuted);
            $userToBeMuted->refresh();
            if ($muteUserResponse) {
                return $this->sendResponse(new UserResource($userToBeMuted), 201);
            }
            return $this->sendError(['message' => __('messages.exception.general')], 400);
        } catch (ModelNotFoundException) {
            return $this->sendError(['message' => 'User not found'], 404);
        } catch (UserAlreadyMutedException) {
            return $this->sendError([
                                        'message' => __(
                                            'user.already-muted',
                                            ['username' => $userToBeMuted->username]
                                        )
                                    ], 409);
        }
    }

    /**
     * @OA\Delete(
     *      path="/user/{id}/mute",
     *      operationId="destroyMute",
     *      tags={"User/Hide and Block"},
     *      summary="Unmute a user",
     *      description="Unmute a specific user. That way they will be shown on your dashboard and in the active
     *      journeys tab again",
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
     *              @OA\Property(property="data", ref="#/components/schemas/User")
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Not logged in"),
     *       @OA\Response(response=409, description="User is not muted"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={
     *           {"passport": {"write-block"}}, {"token": {}}
     *
     *       }
     *     )
     *
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function destroyMute(int $userId): JsonResponse {
        try {
            $userToBeUnmuted    = User::findOrFail($userId);
            $unmuteUserResponse = BackendUserBackend::unmuteUser(auth()->user(), $userToBeUnmuted);
            $userToBeUnmuted->refresh();
            if ($unmuteUserResponse) {
                return $this->sendResponse(new UserResource($userToBeUnmuted));
            }
            return $this->sendError(['message' => __('messages.exception.general')], 400);
        } catch (ModelNotFoundException) {
            return $this->sendError(['message' => 'User not found'], 404);
        } catch (UserNotMutedException) {
            return $this->sendError([
                                        'message' => __(
                                            'user.already-unmuted',
                                            ['username' => $userToBeUnmuted->username]
                                        )
                                    ], 409);
        }
    }

    /**
     * @OA\Get(
     *      path="/user/search/{query}",
     *      operationId="searchUsers",
     *      tags={"User"},
     *      summary="Get paginated statuses for single user",
     *      description="Returns paginated statuses of a single user specified by the username",
     *      @OA\Parameter (
     *           name="query",
     *           in="path",
     *           description="username",
     *           example="Gertrud123",
     *      ),
     *      @OA\Parameter (
     *          name="page",
     *          description="Page of pagination",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
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
     *       security={
     *           {"passport": {"read-search"}}, {"token": {}}
     *       }
     *     )
     *
     */
    public function search(string $query): AnonymousResourceCollection|JsonResponse {
        try {
            return UserResource::collection(BackendUserBackend::searchUser($query));
        } catch (InvalidArgumentException) {
            return $this->sendError(['message' => __('messages.exception.general')], 400);
        }
    }
}
