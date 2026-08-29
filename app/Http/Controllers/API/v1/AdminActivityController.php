<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\Activitylog\Models\Activity;

class AdminActivityController extends Controller
{
    #[OA\Get(
        path: '/v1/admin/activity',
        operationId: 'getAdminActivity',
        description: 'Requires "view activity" permission. Returns the last 3 months of activity log entries, excluding system entries.',
        summary: 'List activity log',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'subjectType', description: 'Full class name to filter by subject type, requires subjectId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'subjectId', description: 'Subject ID to filter by, requires subjectType', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated activity log',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: ActivityLogResource::class)),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('view activity');

        $validated = $request->validate([
            'subjectType' => ['nullable', 'string'],
            'subjectId' => ['nullable', 'integer'],
        ]);

        $query = Activity::with('causer')
            ->orderByDesc('created_at')
            ->where('subject_id', '<>', '1000001')
            ->where('created_at', '>', now()->subMonths(3)->toDateString());

        if (isset($validated['subjectType'], $validated['subjectId'])) {
            $query->where('subject_type', $validated['subjectType'])
                ->where('subject_id', $validated['subjectId']);
        }

        return ActivityLogResource::collection($query->cursorPaginate(15));
    }
}
