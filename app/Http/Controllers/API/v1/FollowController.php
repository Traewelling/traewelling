<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AlreadyFollowingException;
use App\Http\Controllers\Backend\User\FollowController as FollowBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\UserResource;
use App\Models\Follow;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

class FollowController extends Controller
{
    #[OA\Post(
        path: '/v1/user/{id}/follow',
        operationId: 'createFollow',
        summary: 'Follow a user',
        security: [['passport' => ['write-follows']], ['token' => []]],
        tags: ['User/Follow'],
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
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserResource',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 409, description: 'Already following'),
            new OA\Response(response: 403, description: 'User is blocked'),
        ],
    )]
    public function createFollow(string|int $userId): JsonResponse
    {
        try {
            $this->authorize('create', Follow::class);
            $userToFollow = $this->resolveUserByIdOrUuid($userId);
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

    #[OA\Delete(
        path: '/v1/user/{id}/follow',
        operationId: 'destroyFollow',
        summary: 'Unfollow a user',
        security: [['passport' => ['write-follows']], ['token' => []]],
        tags: ['User/Follow'],
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
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserResource',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 409, description: 'Already following'),
        ],
    )]
    public function destroyFollow(string|int $userId): JsonResponse
    {
        try {
            $userToUnfollow = $this->resolveUserByIdOrUuid($userId);
            $destroyFollowResponse = UserBackend::destroyFollow(Auth::user(), $userToUnfollow);
            if ($destroyFollowResponse === false) {
                return $this->sendError(['message' => __('controller.user.follow-404')], 409);
            }
            $userToUnfollow->fresh();

            return $this->sendResponse(new UserResource($userToUnfollow));
        } catch (ModelNotFoundException) {
            return $this->sendError(['message' => 'User not found'], 404);
        } catch (InvalidArgumentException) {
            return $this->sendError(null, 400);
        }
    }

    #[OA\Get(
        path: '/v1/user/self/followers',
        operationId: 'getFollowers',
        summary: 'List all followers',
        security: [['passport' => ['read-settings-followers']], ['token' => []]],
        tags: ['User/Follow', 'Settings'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination',
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
                example: 1,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data', 'links', 'meta'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/UserResource'),
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 409, description: 'Already following'),
        ],
    )]
    public function getFollowers(): AnonymousResourceCollection
    {
        return UserResource::collection(FollowBackend::getFollowers(user: auth()->user()));
    }

    #[OA\Get(
        path: '/v1/user/self/follow-requests',
        operationId: 'getFollowRequests',
        summary: 'List all followers',
        security: [['passport' => ['read-settings-followers']], ['token' => []]],
        tags: ['User/Follow', 'Settings'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination',
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
                example: 1,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data', 'links', 'meta'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/UserResource'),
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ],
                ),
            ),
        ],
    )]
    public function getFollowRequests(): AnonymousResourceCollection
    {
        return UserResource::collection(FollowBackend::getFollowRequests(user: auth()->user()));
    }

    #[OA\Get(
        path: '/v1/user/self/followings',
        operationId: 'getFollowings',
        summary: 'List all users the current user is following',
        security: [['passport' => ['read-settings-followers']], ['token' => []]],
        tags: ['User/Follow', 'Settings'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination',
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
                example: 1,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data', 'links', 'meta'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/UserResource'),
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ],
                ),
            ),
        ],
    )]
    public function getFollowings(): AnonymousResourceCollection
    {
        return UserResource::collection(FollowBackend::getFollowings(user: auth()->user()));
    }

    /**
     * @param  Request  $request
     */
    #[OA\Delete(
        path: '/v1/user/self/followers/{userId}',
        operationId: 'removeFollower',
        summary: 'Remove a follower',
        security: [['passport' => ['write-followers']], ['token' => []]],
        tags: ['User/Follow'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
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
            new OA\Response(response: 200, description: 'successful operation'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Permission denied'),
            new OA\Response(response: 404, description: 'Follow not found'),
            new OA\Response(response: 500, description: 'Unknown error'),
        ],
    )]
    public function removeFollowerByUserId(string|int $userId): JsonResponse
    {
        try {
            $follower = $this->resolveUserByIdOrUuid($userId);
            $follow = Follow::where('user_id', $follower->id)
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
        } catch (AuthorizationException) {
            return $this->sendError('Permission denied', 403);
        }
    }

    /**
     * @param  Request  $request
     */
    #[OA\Put(
        path: '/v1/user/self/follow-requests/{userId}',
        operationId: 'acceptFollowRequest',
        summary: 'Accept a follow request',
        security: [['passport' => ['write-followers']], ['token' => []]],
        tags: ['User/Follow'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
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
            new OA\Response(response: 200, description: 'successful operation'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Permission denied'),
            new OA\Response(response: 404, description: 'Request not found'),
        ],
    )]
    public function approveFollowRequestByUserId(string|int $userId): JsonResponse
    {
        try {
            $requester = $this->resolveUserByIdOrUuid($userId);
            FollowBackend::approveFollower(auth()->user(), $requester);

            return $this->sendResponse();
        } catch (ModelNotFoundException) {
            return $this->sendError('Request not found');
        } catch (Exception) {
            Log::error('APIv1/approveFollowRequest: Could not approve follow request', ['user' => auth()->user(), 'userId' => $userId]);

            return $this->sendError('Unknown error', 500);
        }
    }

    /**
     * @param  Request  $request
     *
     * @todo paths should use kebab-case
     * @todo paths should not use verbs
     */
    #[OA\Delete(
        path: '/v1/user/self/follow-requests/{userId}',
        operationId: 'rejectFollowRequest',
        summary: 'Reject a follow request',
        security: [['passport' => ['write-followers']], ['token' => []]],
        tags: ['User/Follow'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
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
            new OA\Response(response: 200, description: 'successful operation'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Permission denied'),
            new OA\Response(response: 404, description: 'Request not found'),
        ],
    )]
    public function rejectFollowRequestByUserId(string|int $userId): JsonResponse
    {
        try {
            $requester = $this->resolveUserByIdOrUuid($userId);
            FollowBackend::rejectFollower(auth()->user(), $requester);

            return $this->sendResponse();
        } catch (ModelNotFoundException) {
            return $this->sendError('Request not found');
        } catch (Exception) {
            Log::error('APIv1/rejectFollowRequest: Could not reject follow request', ['user' => auth()->user(), 'userId' => $userId]);

            return $this->sendError('Unknown error', 500);
        }
    }
}
