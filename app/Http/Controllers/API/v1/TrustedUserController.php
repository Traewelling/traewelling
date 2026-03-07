<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\User\FriendCheckinSetting;
use App\Http\Resources\TrustedUserResource;
use App\Models\TrustedUser;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use stdClass;

class TrustedUserController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    #[OA\Get(
        path: '/user/{user}/trusted',
        operationId: 'trustedUserIndex',
        description: 'Get all trusted users for the current user or a specific user (admin only).',
        summary: 'Get all trusted users for a user',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'ID of the user (or string \'self\' for current user)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'List of trusted users',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/TrustedUserResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: '401', description: 'Unauthorized'),
            new OA\Response(response: '403', description: 'Forbidden'),
            new OA\Response(response: '404', description: 'User not found'),
            new OA\Response(response: '500', description: 'Internal Server Error'),
        ],
    )]
    public function index(string|int $userIdOrSelf): AnonymousResourceCollection
    {
        $user = $this->getUserOrSelf($userIdOrSelf);
        $this->authorize('update', $user);

        return TrustedUserResource::collection($user->trustedUsers);
    }

    /**
     * @throws AuthorizationException
     */
    #[OA\Get(
        path: '/user/self/trusted-by',
        operationId: 'trustedByUserIndex',
        summary: 'Get all users who trust the current user',
        tags: ['User'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'List of users who trust the current user',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/TrustedUserResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: '401', description: 'Unauthorized'),
            new OA\Response(response: '500', description: 'Internal Server Error'),
        ],
    )]
    public function indexTrustedBy(): AnonymousResourceCollection
    {
        $user = auth()->user();

        $friends = $user?->userFollowers
            ->filter(fn (User $otherUser) => $user->userFollowings->contains($otherUser))
            ->filter(fn (User $otherUser) => $otherUser->friend_checkin === FriendCheckinSetting::FRIENDS);

        $trustedByUsers = $user?->trustedByUsers
            ->reject(fn (TrustedUser $trustedUser) => $trustedUser->user->friend_checkin !== FriendCheckinSetting::LIST)
            ->merge($friends)
            ->map(function (TrustedUser|User $user) { // map data to match the TrustedUserResource
                $std = new stdClass();
                $std->trusted = $user instanceof TrustedUser ? $user->user : $user;
                $std->expires_at = $user instanceof TrustedUser ? $user->expires_at : null;

                return $std;
            })
            ->unique('trusted.id') // remove duplicates
            ->sortBy('trusted.username', SORT_FLAG_CASE | SORT_NATURAL);

        return TrustedUserResource::collection($trustedByUsers);
    }

    /**
     * @throws AuthorizationException
     */
    #[OA\Post(
        path: '/user/{user}/trusted',
        operationId: 'trustedUserStore',
        description: 'Add a user to the trusted users for the current user or a specific user (admin only).',
        summary: 'Add a user to the trusted users for a user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id'],
                properties: [
                    new OA\Property(property: 'userId', type: 'string', description: 'User-ID or UUID', example: '00000000-0000-0000-0000-000000000000'),
                    new OA\Property(
                        property: 'expiresAt',
                        type: 'string',
                        format: 'date-time',
                        example: '2024-07-28T00:00:00Z',
                        nullable: true,
                    ),
                ],
            ),
        ),
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'ID of the user (or string \'self\' for current user) who want\'s to trust.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: '201', description: 'User added to trusted users'),
            new OA\Response(response: '400', description: 'Bad Request'),
            new OA\Response(response: '401', description: 'Unauthorized'),
            new OA\Response(response: '403', description: 'Forbidden'),
            new OA\Response(response: '404', description: 'User not found'),
            new OA\Response(response: '500', description: 'Internal Server Error'),
        ],
    )]
    public function store(Request $request, string|int $userIdOrSelf): Response
    {
        $user = $this->getUserOrSelf($userIdOrSelf);
        $validated = $request->validate([
            'userId' => ['required', 'string'],
            'expiresAt' => ['nullable', 'date', 'after:now'],
        ]);
        $trustedUser = $this->resolveUserByIdOrUuid($validated['userId']);
        $this->authorize('update', $user);
        TrustedUser::updateOrCreate(
            [
                'user_id' => $user->id,
                'trusted_id' => $trustedUser->id,
            ],
            [
                'expires_at' => $validated['expiresAt'] ?? null,
            ]
        );

        return response()->noContent(201, ['Content-Type' => 'application/json']);
    }

    /**
     * @throws AuthorizationException
     */
    #[OA\Delete(
        path: '/user/{user}/trusted/{trusted}',
        operationId: 'trustedUserDestroy',
        description: 'Remove a user from the trusted users for the current user or a specific user (admin only).',
        summary: 'Remove a user from the trusted users for a user',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'ID or UUID of the user (or string \'self\' for current user)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'trusted',
                description: 'ID or UUID of the trusted user',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: '204', description: 'User removed from trusted users'),
            new OA\Response(response: '401', description: 'Unauthorized'),
            new OA\Response(response: '403', description: 'Forbidden'),
            new OA\Response(response: '404', description: 'User not found'),
            new OA\Response(response: '500', description: 'Internal Server Error'),
        ],
    )]
    public function destroy(string|int $userIdOrSelf, string|int $trusted): Response
    {
        $user = $this->getUserOrSelf($userIdOrSelf);
        $trusted = $this->resolveUserByIdOrUuid($trusted);
        $this->authorize('update', $user);
        TrustedUser::where('user_id', $user->id)->where('trusted_id', $trusted->id)->delete();

        return response()->noContent(204, ['Content-Type' => 'application/json']);
    }
}
