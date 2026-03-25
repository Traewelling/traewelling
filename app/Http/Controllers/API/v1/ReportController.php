<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Repositories\ReportRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

class ReportController extends Controller
{
    #[OA\Get(
        path: '/reports',
        operationId: 'listReports',
        summary: 'List all reports. Admin only.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Report'],
        parameters: [
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of reports.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ReportResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
            new OA\Response(response: 403, description: 'Forbidden.'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Report::class);

        $reports = Report::with('reporter')
            ->orderByDesc('created_at')
            ->cursorPaginate(15);

        return ReportResource::collection($reports);
    }

    #[OA\Get(
        path: '/reports/{id}',
        operationId: 'getReport',
        summary: 'Get a single report with activity log. Admin only.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Report'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Report details including activity log.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ReportResource'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
        ],
    )]
    public function show(string $reportId): ReportResource
    {
        $report = Report::with(['reporter', 'activities.causer'])->findOrFail($reportId);
        $this->authorize('view', $report);

        return new ReportResource($report);
    }

    #[OA\Post(
        path: '/reports',
        operationId: 'createReport',
        summary: 'Report a Status, Event or User to the admins.',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['subjectType', 'subjectId', 'reason', 'description'],
                properties: [
                    new OA\Property(
                        property: 'subjectType',
                        type: 'string',
                        example: 'Status',
                        enum: ['Event', 'Status', 'Trip', 'User'],
                    ),
                    new OA\Property(property: 'subjectId', type: 'integer', example: 1),
                    new OA\Property(
                        property: 'reason',
                        type: 'string',
                        example: 'inappropriate',
                        enum: ['inappropriate', 'implausible', 'spam', 'illegal', 'other'],
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        example: 'The status is inappropriate because...',
                    ),
                ],
            ),
        ),
        tags: ['Report'],
        responses: [
            new OA\Response(response: 201, description: 'The report was successfully created.'),
            new OA\Response(response: 401, description: 'The user is not authenticated.'),
            new OA\Response(response: 422, description: 'The given data was invalid.'),
        ],
    )]
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'subjectType' => ['required_without:subject_type', new Enum(ReportableSubject::class)],
            'subjectId' => ['required_without:subject_id', 'integer', 'min:1'],
            'reason' => ['required', new Enum(ReportReason::class)],
            'description' => ['required', 'string', 'min:10'],
        ]);

        new ReportRepository()->createReport(
            subjectType: ReportableSubject::from($validated['subjectType']),
            subjectId: $validated['subjectId'],
            reason: ReportReason::from($validated['reason']),
            description: $validated['description'],
            reporter: auth()->user()
        );

        return response()->noContent(201, ['Content-Type' => 'application/json']);
    }

    #[OA\Put(
        path: '/reports/{id}',
        operationId: 'updateReport',
        summary: 'Update a report status. Admin only.',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['open', 'waiting', 'closed']),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                ],
            ),
        ),
        tags: ['Report'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Report updated.'),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
        ],
    )]
    public function update(Request $request, string $reportId): Response
    {
        $report = Report::findOrFail($reportId);
        $this->authorize('update', $report);

        $validated = $request->validate([
            'status' => ['required', new Enum(ReportStatus::class)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $logger = activity()->causedBy(auth()->user())
            ->performedOn($report);
        if ($validated['status'] !== $report->status->value) {
            $logger->withProperties([
                'attributes' => [
                    'status' => $validated['status'],
                ],
                'old' => [
                    'status' => $report->status,
                ],
            ]);
        }
        $logger->log($validated['description'] ?? '');

        $report->update(['status' => $validated['status']]);

        return response()->noContent();
    }
}
