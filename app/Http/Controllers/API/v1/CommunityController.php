<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\Contribution\CommunityProfileResource;
use App\Http\Resources\Contribution\ContributionHistoryResource;
use App\Models\ContributionHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Community', description: 'Community contribution system endpoints')]
class CommunityController extends Controller
{
    #[OA\Get(
        path: '/community/profile',
        operationId: 'getCommunityProfile',
        tags: ['Community'],
        summary: 'Get your contribution profile',
        description: 'Returns contribution XP, level, and progress information for the authenticated user',
        security: [['passport' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/CommunityProfile',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function getMyProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->sendResponse(new CommunityProfileResource($user));
    }

    #[OA\Get(
        path: '/community/history',
        operationId: 'getCommunityHistory',
        tags: ['Community'],
        summary: 'Get your contribution history',
        description: 'Returns a cursor-paginated list of contribution history entries for the authenticated user',
        security: [['passport' => []]],
        parameters: [
            new OA\Parameter(
                name: 'cursor',
                in: 'query',
                description: 'Cursor for pagination',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Number of entries per page (min 5, max 50, default 15)',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 5, maximum: 50, default: 15),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ContributionHistory'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function getHistory(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $limit = (int) ($validated['limit'] ?? 15);

        $history = ContributionHistory::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->cursorPaginate($limit);

        return ContributionHistoryResource::collection($history);
    }
}
