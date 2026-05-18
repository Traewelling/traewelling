<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Requests\StoreAlertRequest;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use App\Models\AlertTranslation;
use App\Services\PrivacyPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class AlertController extends Controller
{
    public function __construct(
        private readonly PrivacyPolicyService $privacyPolicyService,
    ) {}

    #[OA\Get(
        path: '/alerts',
        operationId: 'getAlerts',
        summary: 'Get alerts. Without ?all returns only currently active alerts. With ?all=true (admin only) returns all alerts with cursor pagination.',
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'all', description: 'Admin only: return all alerts regardless of active dates.', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of alerts',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/AlertResource'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Alert::class);

        // Admin-only: return all alerts regardless of active dates, with cursor pagination
        if ($request->boolean('all') && Gate::allows('create', Alert::class)) {
            return AlertResource::collection(
                Alert::with('translations')
                    ->orderByDesc('active_from')
                    ->cursorPaginate(25)
            );
        }

        $now = now()->startOfDay();

        $alerts = Alert::with('translations')
            ->where('active_from', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->where('active_until', '>=', $now)
                    ->orWhereNull('active_until');
            })
            ->orderByDesc('active_from')
            ->orderByDesc('active_until')
            ->get();

        // if year in review is active, inject the special alert
        if (config('trwl.year_in_review.alert')) {
            $alert = new Alert();
            $alert->id = 'year-in-review-' . date('Y');
            $alert->type = 'info';
            $alert->active_from = now()->startOfYear();
            $alert->active_until = now()->endOfYear();

            $translation = new AlertTranslation();
            $translation->locale = app()->getLocale();
            $translation->title = __('year-review');
            $translation->content = __('year-review.teaser');
            $translation->url = url('/year-in-review');
            $alert->setRelation('translations', collect([$translation]));

            $alerts->prepend($alert);
        }

        // TODO: remove following alert injection after 2026-05-31
        $deadline = Carbon::create(2026, 5, 31)->endOfDay();
        $userId = auth()->id();
        if (now()->lte($deadline) && $userId !== null) {
            $hasDuplicates = Cache::remember(
                'duplicate-checkins-check:' . $userId,
                now()->addMinutes(5),
                function () use ($userId): bool {
                    return DB::table(
                        DB::table('train_checkins')
                            ->select('trip_id')
                            ->where('user_id', $userId)
                            ->whereNotNull('origin_stopover_id')
                            ->groupBy('trip_id', 'origin_stopover_id')
                            ->havingRaw('COUNT(*) > 1')
                            ->limit(1),
                        'dups'
                    )->exists();
                }
            );

            if ($hasDuplicates) {
                $alert = new Alert();
                $alert->id = 'duplicate-checkins-cleanup';
                $alert->type = 'warning';
                $alert->active_from = Carbon::create(2026, 5, 6);
                $alert->active_until = $deadline;
                $alert->url = url('/statuses/duplicates');

                $translation = new AlertTranslation();
                $translation->locale = app()->getLocale();
                $translation->title = __('checkin.duplicates.alert.title');
                $translation->content = __('checkin.duplicates.alert.content');
                $translation->url = url('/statuses/duplicates');
                $alert->setRelation('translations', collect([$translation]));

                $alerts->prepend($alert);
            }
        }

        $upcomingPolicy = $this->privacyPolicyService->getUpcomingPolicy();
        $user = auth()->user();

        if ($upcomingPolicy !== null && $user !== null && !$this->privacyPolicyService->hasUserAcceptedPolicy($user, $upcomingPolicy)) {
            $alert = new Alert();
            $alert->id = 'privacy-policy-upcoming';
            $alert->type = 'warning';
            $alert->active_from = now();
            $alert->active_until = $upcomingPolicy->valid_at;

            $translation = new AlertTranslation();
            $translation->locale = app()->getLocale();
            $translation->title = __('privacy.upcoming-alert.title');
            $translation->content = __('privacy.upcoming-alert.content', [
                'date' => $upcomingPolicy->valid_at->isoFormat('LL'),
            ]);
            $translation->url = url('/gdpr-intercept');
            $alert->setRelation('translations', collect([$translation]));

            $alerts->prepend($alert);
        }

        return AlertResource::collection($alerts);
    }

    #[OA\Get(
        path: '/alerts/{id}',
        operationId: 'getAlert',
        summary: 'Get a single alert. Admin only.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Alert details.', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AlertResource')])),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
        ],
    )]
    public function show(Alert $alert): AlertResource
    {
        $this->authorize('update', $alert);

        return new AlertResource($alert->load('translations'));
    }

    #[OA\Post(
        path: '/alerts',
        operationId: 'createAlert',
        summary: 'Create a new alert. Admin only.',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'active_from', 'title_de', 'content_de', 'title_en', 'content_en'],
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['info', 'warning', 'danger', 'success']),
                    new OA\Property(property: 'active_from', type: 'string', format: 'date'),
                    new OA\Property(property: 'active_until', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'title_de', type: 'string'),
                    new OA\Property(property: 'content_de', type: 'string'),
                    new OA\Property(property: 'title_en', type: 'string'),
                    new OA\Property(property: 'content_en', type: 'string'),
                    new OA\Property(property: 'url_de', type: 'string', nullable: true),
                    new OA\Property(property: 'url_en', type: 'string', nullable: true),
                    new OA\Property(property: 'url', type: 'string', nullable: true),
                ],
            ),
        ),
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 201, description: 'Alert created.', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AlertResource')])),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function store(StoreAlertRequest $request): AlertResource
    {
        $this->authorize('create', Alert::class);
        $alert = new Alert();
        $this->updateOrCreate($request, $alert);

        return new AlertResource($alert);
    }

    #[OA\Put(
        path: '/alerts/{id}',
        operationId: 'updateAlert',
        summary: 'Update an alert. Admin only.',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'active_from', 'title_de', 'content_de', 'title_en', 'content_en'],
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['info', 'warning', 'danger', 'success']),
                    new OA\Property(property: 'active_from', type: 'string', format: 'date'),
                    new OA\Property(property: 'active_until', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'title_de', type: 'string'),
                    new OA\Property(property: 'content_de', type: 'string'),
                    new OA\Property(property: 'title_en', type: 'string'),
                    new OA\Property(property: 'content_en', type: 'string'),
                    new OA\Property(property: 'url_de', type: 'string', nullable: true),
                    new OA\Property(property: 'url_en', type: 'string', nullable: true),
                    new OA\Property(property: 'url', type: 'string', nullable: true),
                ],
            ),
        ),
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Alert updated.', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AlertResource')])),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function update(StoreAlertRequest $request, string $id): AlertResource
    {
        $alert = Alert::findOrFail($id);
        $this->authorize('update', $alert);
        $this->updateOrCreate($request, $alert);

        return new AlertResource($alert);
    }

    #[OA\Delete(
        path: '/alerts/{id}',
        operationId: 'deleteAlert',
        summary: 'Delete an alert. Admin only.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Alert deleted.'),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
        ],
    )]
    public function destroy(Alert $alert): JsonResponse
    {
        $this->authorize('delete', $alert);
        $alert->delete();

        return response()->json(null, 204);
    }

    private function updateOrCreate(StoreAlertRequest $request, Alert $alert): void
    {
        DB::beginTransaction();
        $alert->type = $request->type;
        $alert->active_from = $request->active_from;
        $alert->active_until = $request->active_until;
        $alert->url = $request->url;
        $alert->save();

        $alert->translations()->updateOrCreate(
            ['locale' => 'de'],
            [
                'title' => $request->title_de,
                'content' => $request->content_de,
                'url' => $request->url_de,
            ]
        );

        $alert->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'title' => $request->title_en,
                'content' => $request->content_en,
                'url' => $request->url_en,
            ]
        );

        DB::commit();
    }
}
