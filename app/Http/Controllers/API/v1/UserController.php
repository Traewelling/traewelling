<?php

namespace App\Http\Controllers\API\v1;


use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotBlockedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\Backend\User\BlockController;
use App\Http\Controllers\Backend\UserController as BackendUserBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Error;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
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
     *           {"token": {}},
     *           {}
     *       }
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAccount(Request $request): JsonResponse {
        $request->validate(['confirmation' => ['required', Rule::in([auth()->user()->username])]]);

        try {
            return $this->sendResponse(BackendUserBackend::deleteUserAccount(user: auth()->user()));
        } catch (Error) {
            return $this->sendError('', 409);
        }
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
     *                      ref="#/components/schemas/Status"
     *                  )
     *              ),
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       security={
     *           {"token": {}}
     *       },
     *       @OA\Response(response=403, description="Forbidden, User is blocked"),
     *       security={
     *           {"token": {}}
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
            abort(404, $exception->response()->message() ?? 'No statuses found, or statuses are not visible to you.');
        }
        return StatusResource::collection($userResponse);
    }

    //ToDo: Is this even used anywhere?
    public function authenticated(): UserResource {
        return new UserResource(Auth::user());
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
     *           {"token": {}}
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
     *      path="/user/createBlock",
     *      operationId="createBlock",
     *      tags={"Hide and Block"},
     *      summary="Block a user",
     *      description="Block a specific user. That user will not be able to see your statuses or profile information,
     *      and cannot send you follow requests. Public statuses are still visible through the incognito mode.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  title="userId",
     *                  format="int64",
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
     *       @OA\Response(response=409, description="User is already blocked"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createBlock(Request $request): JsonResponse {
        $validated       = $request->validate([
                                                  'userId' => [
                                                      'required',
                                                      'exists:users,id',
                                                      Rule::notIn([auth()->user()->id]),
                                                  ]
                                              ]);
        $userToBeBlocked = User::find($validated['userId']);

        try {
            $blockUserResponse = BackendUserBackend::blockUser(auth()->user(), $userToBeBlocked);
        } catch (UserAlreadyMutedException) {
            return $this->sendError([
                                        'message' => __(
                                            'user.already-blocked',
                                            ['username' => $userToBeBlocked->username]
                                        )
                                    ], 409);
        }

        $userToBeBlocked->refresh();
        if ($blockUserResponse) {
            return $this->sendResponse(new UserResource($userToBeBlocked), 201);
        }
        return $this->sendError(['message' => __('messages.exception.general')], 400);
    }

    /**
     * @OA\Delete(
     *      path="/user/destroyBlock",
     *      operationId="destroyBlock",
     *      tags={"Hide and Block"},
     *      summary="Unmute a user",
     *      description="Unblock a specific user. They are now able to see your statuses and profile information again,
     *      and send you follow requests.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  title="userId",
     *                  format="int64",
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
     *       @OA\Response(response=409, description="User is not blocked"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function destroyBlock(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'userId' => [
                                                'required',
                                                'exists:users,id',
                                            ]
                                        ]);

        $userToBeUnblocked = User::find($validated['userId']);

        try {
            $unblockUserResponse = BackendUserBackend::unblockUser(auth()->user(), $userToBeUnblocked);

        } catch (UserNotBlockedException) {
            return $this->sendError([
                                        'message' => __(
                                            'user.already-unblocked',
                                            ['username' => $userToBeUnblocked->username]
                                        )
                                    ], 409);
        }

        $userToBeUnblocked->refresh();
        if ($unblockUserResponse) {
            return $this->sendResponse(new UserResource($userToBeUnblocked));
        }
        return $this->sendError(['message' => __('messages.exception.general')], 400);
    }

    /**
     * @OA\Post(
     *      path="/user/createMute",
     *      operationId="createMute",
     *      tags={"Hide and Block"},
     *      summary="Mute a user",
     *      description="Mute a specific user. That way they will not be shown on your dashboard and in the active
     *      journeys tab",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  title="userId",
     *                  format="int64",
     *                  description="ID of the to-be-muted user",
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
     *       @OA\Response(response=409, description="User is already muted"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
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
            $muteUserResponse = BackendUserBackend::muteUser(auth()->user(), $userToBeMuted);
        } catch (UserAlreadyMutedException) {
            return $this->sendError([
                                        'message' => __(
                                            'user.already-muted',
                                            ['username' => $userToBeMuted->username]
                                        )
                                    ], 409);
        }

        $userToBeMuted->refresh();
        if ($muteUserResponse) {
            return $this->sendResponse(new UserResource($userToBeMuted), 201);
        }
        return $this->sendError(['message' => __('messages.exception.general')], 400);
    }

    /**
     * @OA\Delete(
     *      path="/user/destroyMute",
     *      operationId="destroyMute",
     *      tags={"Hide and Block"},
     *      summary="Unmute a user",
     *      description="Unmute a specific user. That way they will be shown on your dashboard and in the active
     *      journeys tab again",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  title="userId",
     *                  format="int64",
     *                  description="ID of the to-be-unmuted user",
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
     *       @OA\Response(response=409, description="User is not muted"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function destroyMute(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'userId' => [
                                                'required',
                                                'exists:users,id',
                                            ]
                                        ]);

        $userToBeUnmuted = User::find($validated['userId']);

        try {
            $unmuteUserResponse = BackendUserBackend::unmuteUser(auth()->user(), $userToBeUnmuted);

        } catch (UserNotMutedException) {
            return $this->sendError([
                                        'message' => __(
                                            'user.already-unmuted',
                                            ['username' => $userToBeUnmuted->username]
                                        )
                                    ], 409);
        }

        $userToBeUnmuted->refresh();
        if ($unmuteUserResponse) {
            return $this->sendResponse(new UserResource($userToBeUnmuted));
        }
        return $this->sendError(['message' => __('messages.exception.general')], 400);
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
     *           {"token": {}}
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
