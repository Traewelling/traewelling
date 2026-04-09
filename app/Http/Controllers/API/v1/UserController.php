<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\UserAlreadyBlockedException;
use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotBlockedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\Backend\UserController as BackendUserBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\LightUserResource;
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
        description: 'Deletes the Account for the user and all posts created by it',
        summary: 'Delete User Account',
        security: [['passport' => ['extra-delete']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'confirmation',
                        title: 'confirmation',
                        description: 'Username of the to be deleted account (needs to match the currently logged in user)',
                        example: 'Gertrud123',
                    ),
                ],
            ),
        ),
        tags: ['Settings'],
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
        description: 'Returns paginated statuses of a single user specified by the username',
        summary: '[Auth optional] Get paginated statuses for single user',
        security: [['passport' => []], ['token' => []], ['passport' => ['read-statuses']], ['token' => []]],
        tags: ['User', 'Status'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                description: 'username',
                in: 'path',
                example: 'Gertrud123',
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                in: 'query',
                required: false,
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
        description: 'Returns general information, metadata and statistics for a user',
        summary: '[Auth optional] Get information for single user',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                description: 'username',
                in: 'path',
                example: 'Gertrud123',
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                in: 'query',
                required: false,
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
        description: 'Block a specific user. That user will not be able to see your statuses or profile information, and cannot send you follow requests. Public statuses are still visible through the incognito mode.',
        summary: 'Block a user',
        security: [['passport' => ['write-block']], ['token' => []]],
        tags: ['User/Hide and Block'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User-ID or UUID',
                in: 'path',
                schema: new OA\Schema(oneOf: [
                    new OA\Schema(type: 'string', format: 'uuid'),
                    new OA\Schema(type: 'integer'),
                ]),
                example: '00000000-0000-0000-0000-000000000000',
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
    public function createBlock(string|int $userId): JsonResponse
    {
        try {
            $userToBeBlocked = $this->resolveUserByIdOrUuid($userId);
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
        description: 'Unblock a specific user. They are now able to see your statuses and profile information again, and send you follow requests.',
        summary: 'Unmute a user',
        security: [['passport' => ['write-block']], ['token' => []]],
        tags: ['User/Hide and Block'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User-ID or UUID',
                in: 'path',
                schema: new OA\Schema(oneOf: [
                    new OA\Schema(type: 'string', format: 'uuid'),
                    new OA\Schema(type: 'integer'),
                ]),
                example: '00000000-0000-0000-0000-000000000000',
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
    public function destroyBlock(string|int $userId): JsonResponse
    {
        try {
            $userToBeUnblocked = $this->resolveUserByIdOrUuid($userId);
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
        description: 'Mute a specific user. That way they will not be shown on your dashboard and in the active journeys tab',
        summary: 'Mute a user',
        security: [['passport' => ['write-block']], ['token' => []]],
        tags: ['User/Hide and Block'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User-ID or UUID',
                in: 'path',
                schema: new OA\Schema(oneOf: [
                    new OA\Schema(type: 'string', format: 'uuid'),
                    new OA\Schema(type: 'integer'),
                ]),
                example: '00000000-0000-0000-0000-000000000000',
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
    public function createMute(string|int $userId): JsonResponse
    {
        try {
            $userToBeMuted = $this->resolveUserByIdOrUuid($userId);
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
        description: 'Unmute a specific user. That way they will be shown on your dashboard and in the active journeys tab again',
        summary: 'Unmute a user',
        security: [['passport' => ['write-block']], ['token' => []]],
        tags: ['User/Hide and Block'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User-ID or UUID',
                in: 'path',
                schema: new OA\Schema(oneOf: [
                    new OA\Schema(type: 'string', format: 'uuid'),
                    new OA\Schema(type: 'integer'),
                ]),
                example: '00000000-0000-0000-0000-000000000000',
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
    public function destroyMute(string|int $userId): JsonResponse
    {
        try {
            $userToBeUnmuted = $this->resolveUserByIdOrUuid($userId);
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
        path: '/users/self/blocks',
        operationId: 'getBlockedUsers',
        description: 'Returns all users blocked by the authenticated user.',
        summary: 'List blocked users',
        security: [['passport' => ['write-blocks']], ['token' => []]],
        tags: ['User/Hide and Block'],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: LightUserResource::class))],
                )
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
        ],
    )]
    public function getBlockedUsers(): AnonymousResourceCollection
    {
        return LightUserResource::collection(auth()->user()->blockedUsers()->cursorPaginate());
    }

    #[OA\Get(
        path: '/users/self/mutes',
        operationId: 'getMutedUsers',
        description: 'Returns all users muted by the authenticated user.',
        summary: 'List muted users',
        security: [['passport' => ['write-blocks']], ['token' => []]],
        tags: ['User/Hide and Block'],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: LightUserResource::class))],
                )
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
        ],
    )]
    public function getMutedUsers(): AnonymousResourceCollection
    {
        return LightUserResource::collection(auth()->user()->mutedUsers()->cursorPaginate());
    }

    #[OA\Get(
        path: '/user/search/{query}',
        operationId: 'searchUsers',
        description: 'Returns paginated search results for a user based on the given query.',
        summary: 'Get paginated search results for combined search on username and (display)name',
        security: [['passport' => ['read-search']], ['token' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'query',
                description: 'If this is given, the search will be performed on the username and (display)name (or-search)',
                in: 'path',
                example: 'Gertrud123',
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                in: 'query',
                required: false,
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
        description: 'Returns paginated search results for users based on the given parameters.',
        summary: 'Get paginated search results for users by either username or (display)name',
        security: [['passport' => ['read-search']], ['token' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'username',
                description: 'Search for parts username',
                in: 'query',
                required: false,
            ),
            new OA\Parameter(
                name: 'name',
                description: 'Search for parts of users (display)name',
                in: 'query',
                required: false,
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
