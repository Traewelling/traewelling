<?php

namespace App\Http\Controllers\API\v1;


use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\API\ResponseController;
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

class UserController extends ResponseController
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
            return $this->sendv1Response(BackendUserBackend::deleteUserAccount(user: auth()->user()));
        } catch (Error) {
            return $this->sendError('', 409);
        }
    }

    /**
     *   @OA\Get(
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
     *       @OA\Response(response=401, description="Not logged in"),
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
            $userResponse = UserBackend::statusesForUser(user: $user, limit: $validated['limit'] ?? null);
        } catch (AuthorizationException) {
            abort(404, 'No statuses found, or statuses are not visible to you.');
        }
        return StatusResource::collection($userResponse);
    }

   //ToDo: Is this even used anywhere?
    public function authenticated(): UserResource {
        return new UserResource(Auth::user());
    }

    /**
     *
     *   @OA\Get(
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
     *       @OA\Response(response=404, description="User not found"),
     *       @OA\Response(response=401, description="Not logged in"),
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
        return new UserResource(User::where('username', 'like', $username)->firstOrFail());
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
            $muteUserResponse = BackendUserBackend::muteUser(auth()->user(), $userToBeMuted);
        } catch (UserAlreadyMutedException) {
            return $this->sendv1Error([
                                          'message' => __(
                                              'user.already-muted',
                                              ['username' => $userToBeMuted->username]
                                          )
                                      ], 409);
        }

        $userToBeMuted->refresh();
        if ($muteUserResponse) {
            return $this->sendv1Response(new UserResource($userToBeMuted), 201);
        }
        return $this->sendv1Error(['message' => __('messages.exception.general')], 400);
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
            $unmuteUserResponse = BackendUserBackend::unmuteUser(auth()->user(), $userToBeUnmuted);

        } catch (UserNotMutedException) {
            return $this->sendv1Error([
                                          'message' => __(
                                              'user.already-unmuted',
                                              ['username' => $userToBeUnmuted->username]
                                          )
                                      ], 409);
        }

        $userToBeUnmuted->refresh();
        if ($unmuteUserResponse) {
            return $this->sendv1Response(new UserResource($userToBeUnmuted));
        }
        return $this->sendv1Error(['message' => __('messages.exception.general')], 400);
    }

    public function search(string $query): AnonymousResourceCollection|JsonResponse {
        try {
            return UserResource::collection(BackendUserBackend::searchUser($query));
        } catch (InvalidArgumentException) {
            return $this->sendv1Error(['message' => __('messages.exception.general')], 400);
        }
    }
}
