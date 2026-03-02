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
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Delete(
        path: '/settings/account',
        operationId: 'deleteUserAccount',
        tags: ['Settings'],
        summary: 'Delete User Account',
        description: 'Deletes the Account for the user and all posts created by it',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'confirmation',
                        title: 'confirmation',
                        description: 'Username of the to be deleted account (needs to match the currently logged in
user)',
                        example: 'Gertrud123',
                    ),
                ],
            ),
        ),
        security: [['passport' => ['extra-delete']], ['token' => []]],
        responses: [
            new OA\Response(response: 200, description: 'successful operation'),
            new OA\Response(
                response: 409,
                description: 'Conflict. This should not happen but it tries to prevent a 500.',
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Not logged in'),
            new OA\Response(response: 403, description: 'User not authorized to do this action'),
        ],
    )]
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate(['confirmation' => ['required', Rule::in([auth()->user()->username])]]);

        if (!BackendUserBackend::deleteUserAccount(user: auth()->user())) {
            return $this->sendError(__('messages.exception.general'), 500);
        }

        return $this->sendResponse(true);
    }

    #[OA\Get(
        path: '/user/{username}/statuses',
        operationId: 'getStatusesForUser',
        tags: ['User', 'Status'],
        summary: '[Auth optional] Get paginated statuses for single user',
        description: 'Returns paginated statuses of a single user specified by the username',
        security: [['passport' => []], ['token' => []], ['passport' => ['read-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                description: 'username',
                example: 'Gertrud123',
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                required: false,
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StatusResource'),
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                        new OA\Property(
                            property: 'meta',
                            ref: '#/components/schemas/PaginationMeta',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Forbidden, User is blocked'),
        ],
    )]
    public function statuses(Request $request, string $username): AnonymousResourceCollection
    {
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
     * @todo Maybe put this into another method?
     */
    #[OA\Get(
        path: '/user/{username}',
        operationId: 'showUser',
        tags: ['User'],
        summary: '[Auth optional] Get information for single user',
        description: 'Returns general information, metadata and statistics for a user',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                description: 'username',
                example: 'Gertrud123',
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                required: false,
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(
                response: 403,
                description: 'Forbidden, User is blocked',
                content: new OA\JsonContent(
                    required: ['message'],
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'User not accessible.',
                        ),
                        new OA\Property(
                            property: 'reason',
                            ref: '#/components/schemas/ViewUserForbiddenReason',
                        ),
                        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'User not found'),
        ],
    )]
    public function show(string $username): UserResource
    {
        $user = User::where('username', 'like', $username)->firstOrFail();

        try {
            $this->authorize('view', $user);
        } catch (AuthorizationException $exception) {

            $this->abort(
                403,
                $exception->response()->message() ?? 'User not accessible.',
                [
                    'reason' => $exception->response()->status(),
                    'user' => new UserResource($user),
                ]
            );
        }

        return new UserResource($user);
    }

    #[OA\Post(
        path: '/user/{id}/block',
        operationId: 'createBlock',
        tags: ['User/Hide and Block'],
        summary: 'Block a user',
        description: 'Block a specific user. That user will not be able to see your statuses or profile information,
and cannot send you follow requests. Public statuses are still visible through the incognito mode.',
        security: [['passport' => ['write-block']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'User-ID',
                example: 1337,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Not logged in'),
            new OA\Response(response: 403, description: 'User not authorized'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 409, description: 'User is already blocked'),
        ],
    )]
    public function createBlock(int $userId): JsonResponse
    {
        try {
            $userToBeBlocked = User::findOrFail($userId);
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
                ),
            ], 409);
        }
    }

    #[OA\Delete(
        path: '/user/{id}/block',
        operationId: 'destroyBlock',
        tags: ['User/Hide and Block'],
        summary: 'Unmute a user',
        description: 'Unblock a specific user. They are now able to see your statuses and profile information again,
and send you follow requests.',
        security: [['passport' => ['write-block']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'User-ID',
                example: 1337,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Not logged in'),
            new OA\Response(response: 403, description: 'User not authorized'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 409, description: 'User is not blocked'),
        ],
    )]
    public function destroyBlock(int $userId): JsonResponse
    {
        try {
            $userToBeUnblocked = User::findOrFail($userId);
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
                ),
            ], 409);
        }
    }

    #[OA\Post(
        path: '/user/{id}/mute',
        operationId: 'createMute',
        tags: ['User/Hide and Block'],
        summary: 'Mute a user',
        description: 'Mute a specific user. That way they will not be shown on your dashboard and in the active
journeys tab',
        security: [['passport' => ['write-block']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'User-ID',
                example: 1337,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Not logged in'),
            new OA\Response(response: 409, description: 'User is already muted'),
            new OA\Response(response: 403, description: 'User not authorized'),
        ],
    )]
    public function createMute(int $userId): JsonResponse
    {
        try {
            $userToBeMuted = User::findOrFail($userId);
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
                ),
            ], 409);
        }
    }

    #[OA\Delete(
        path: '/user/{id}/mute',
        operationId: 'destroyMute',
        tags: ['User/Hide and Block'],
        summary: 'Unmute a user',
        description: 'Unmute a specific user. That way they will be shown on your dashboard and in the active
journeys tab again',
        security: [['passport' => ['write-block']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'User-ID',
                example: 1337,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Not logged in'),
            new OA\Response(response: 409, description: 'User is not muted'),
            new OA\Response(response: 403, description: 'User not authorized'),
        ],
    )]
    public function destroyMute(int $userId): JsonResponse
    {
        try {
            $userToBeUnmuted = User::findOrFail($userId);
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
                ),
            ], 409);
        }
    }

    #[OA\Get(
        path: '/user/search/{query}',
        operationId: 'searchUsers',
        tags: ['User'],
        summary: 'Get paginated search results for combined search on username and (display)name',
        description: 'Returns paginated search results for a user based on the given query.',
        security: [['passport' => ['read-search']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'query',
                in: 'path',
                description: 'If this is given, the search will be performed on the username and (display)name (or-search)',
                example: 'Gertrud123',
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                required: false,
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/UserResource'),
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                        new OA\Property(
                            property: 'meta',
                            ref: '#/components/schemas/PaginationMeta',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
        ],
    )]
    public function search(string $query): AnonymousResourceCollection|JsonResponse
    {
        try {
            return UserResource::collection(BackendUserBackend::searchUser($query));
        } catch (InvalidArgumentException) {
            return $this->sendError(['message' => __('messages.exception.general')], 400);
        }
    }

    #[OA\Get(
        path: '/user/search',
        operationId: 'searchUsersByParameters',
        tags: ['User'],
        summary: 'Get paginated search results for users by either username or (display)name',
        description: 'Returns paginated search results for users based on the given parameters.',
        security: [['passport' => ['read-search']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                required: false,
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'username',
                in: 'query',
                required: false,
                description: 'Search for parts username',
            ),
            new OA\Parameter(
                name: 'name',
                in: 'query',
                required: false,
                description: 'Search for parts of users (display)name',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/UserResource'),
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                        new OA\Property(
                            property: 'meta',
                            ref: '#/components/schemas/PaginationMeta',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
        ],
    )]
    public function searchByParameters(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $validated = $request->validate([
                'username' => ['nullable', 'string', 'max:255'],
                'name' => ['nullable', 'string', 'max:255'],
            ]);
            if (empty($validated)) {
                return response()->json(null, 400);
            }

            $users = User::query();

            if (isset($validated['username'])) {
                $users->where('username', 'like', "%{$validated['username']}%");
            }

            if (isset($validated['name'])) {
                $users->where('name', 'like', "%{$validated['name']}%");
            }

            return UserResource::collection($users->simplePaginate(10));

        } catch (InvalidArgumentException) {
            return $this->sendError(['message' => __('messages.exception.general')], 400);
        }
    }
}
