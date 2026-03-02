<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\RateLimitExceededException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Resources\UserResource;
use App\Models\Like;
use App\Models\Status;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

class LikesController extends Controller
{
    /**
     * @todo maybe put this in separate controller?
     */
    #[OA\Get(
        path: '/status/{id}/likes',
        operationId: 'getLikesForStatus',
        tags: ['Likes'],
        summary: '[Auth optional] Get likes for status',
        description: 'Returns array of users that liked the status. Can return an empty dataset when the status author or the requesting user has deactivated likes',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Status-ID',
                example: 1337,
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
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(response: 403, description: 'User not authorized to access this status'),
        ],
    )]
    public function show(int $statusId): AnonymousResourceCollection
    {
        $status = Status::with('likes.user')->findOrFail($statusId);

        if (!Gate::allows('like', $status)) {
            // Return empty array if current user or status owner disabled likes
            return UserResource::collection([]);
        }

        return UserResource::collection($status->likes->pluck('user'));
    }

    #[OA\Post(
        path: '/status/{id}/like',
        operationId: 'addLikeToStatus',
        tags: ['Likes'],
        summary: 'Add like to status',
        description: 'Add like to status',
        security: [['passport' => ['write-likes']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Status-ID',
                example: 1337,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/LikeResponse',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'User not authorized to access this status'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(response: 409, description: 'Status already liked by user'),
            new OA\Response(response: 429, description: 'Rate limit exceeded'),
        ],
    )]
    public function create(int $statusId): JsonResponse
    {
        try {
            $this->authorize('create', Like::class);
            $status = Status::findOrFail($statusId);
            StatusBackend::createLike(Auth::user(), $status);

            return $this->sendResponse(
                data: ['count' => $status->likes->count()],
                code: 201,
            );
        } catch (StatusAlreadyLikedException) {
            return $this->sendError(
                error: __('controller.status.like-already'),
                code: 409,
            );
        } catch (AuthorizationException) {
            return $this->sendError(code: 403);
        } catch (ModelNotFoundException) {
            return $this->sendError(code: 404);
        } catch (RateLimitExceededException $exception) {
            return response()->json(null, 429, [
                'X-RateLimit-Limit' => $exception->limit,
                'X-RateLimit-Remaining' => $exception->remaining,
                'X-RateLimit-Reset' => $exception->reset,
            ]);
        }
    }

    #[OA\Delete(
        path: '/status/{id}/like',
        operationId: 'removeLikeFromStatus',
        tags: ['Likes'],
        summary: 'Remove like from status',
        description: 'Removes like from status',
        security: [['passport' => ['write-likes']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Status-ID',
                example: 1337,
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
                            type: 'object',
                            ref: '#/components/schemas/LikeResponse',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No status found for this id'),
        ],
    )]
    public function destroy(int $statusId): JsonResponse
    {
        try {
            $status = Status::findOrFail($statusId);
            StatusBackend::destroyLike(Auth::user(), $statusId);
            $status->refresh();

            return $this->sendResponse(
                data: ['count' => $status->likes->count()],
            );
        } catch (InvalidArgumentException|ModelNotFoundException) {
            return $this->sendError('No status found for this id');
        }
    }
}
