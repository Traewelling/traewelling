<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\EventRejectionReason;
use App\Http\Resources\EventAdminResource;
use App\Http\Resources\EventSuggestionResource;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\Station;
use App\Repositories\EventRepository;
use App\Services\Event\EventService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

class AdminEventSuggestionController extends Controller
{
    private EventService $eventService;

    private EventRepository $eventRepository;

    public function __construct()
    {
        $this->eventService = new EventService();
        $this->eventRepository = new EventRepository();
    }

    #[OA\Get(
        path: '/admin/event-suggestions',
        operationId: 'getAdminEventSuggestions',
        summary: 'List unprocessed event suggestions.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of suggestions.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/EventSuggestionResource')),
                ]),
            ),
            new OA\Response(response: 403, description: 'Forbidden.'),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAnySuggestion', Event::class);

        return EventSuggestionResource::collection($this->eventRepository->paginateOpenSuggestions());
    }

    #[OA\Get(
        path: '/admin/event-suggestions/{id}',
        operationId: 'getAdminEventSuggestion',
        summary: 'Get a single suggestion with parallel events for the accept view.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Suggestion with parallel events.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'suggestion', ref: '#/components/schemas/EventSuggestionResource'),
                            new OA\Property(
                                property: 'parallelEvents',
                                type: 'array',
                                items: new OA\Items(properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(property: 'slug', type: 'string'),
                                    new OA\Property(property: 'checkin_start', type: 'string', format: 'date'),
                                    new OA\Property(property: 'checkin_end', type: 'string', format: 'date'),
                                    new OA\Property(property: 'similarity', type: 'number', format: 'float'),
                                ]),
                            ),
                        ],
                        type: 'object',
                    ),
                ]),
            ),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
        ],
    )]
    public function show(int $id): JsonResponse
    {
        $suggestion = EventSuggestion::with(['user', 'station'])->findOrFail($id);
        $this->authorize('acceptSuggestion', $suggestion);

        $parallelEvents = $this->eventRepository->findParallelEventsWithSimilarity($suggestion)
            ->map(fn (Event $event): array => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'checkin_start' => $event->checkin_start->toDateString(),
                'checkin_end' => $event->checkin_end->toDateString(),
                'similarity' => round($event->similarity, 1),
            ])
            ->values();

        return response()->json([
            'data' => [
                'suggestion' => new EventSuggestionResource($suggestion),
                'parallelEvents' => $parallelEvents,
            ],
        ]);
    }

    #[OA\Post(
        path: '/admin/event-suggestions/{id}/accept',
        operationId: 'acceptAdminEventSuggestion',
        summary: 'Accept an event suggestion and create the event.',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AdminEventRequest')),
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Event created from suggestion.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/EventAdminResource'),
                ]),
            ),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function accept(int $id, Request $request): EventAdminResource
    {
        $suggestion = EventSuggestion::with(['user', 'station'])->findOrFail($id);
        $this->authorize('acceptSuggestion', $suggestion);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hashtag' => ['nullable', 'string', 'max:30'],
            'host' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'station_id' => ['nullable', 'integer', 'exists:train_stations,id'],
            'checkin_start' => ['required', 'date'],
            'checkin_end' => ['required', 'date', 'after_or_equal:checkin_start'],
            'event_start' => ['nullable', 'date', 'after_or_equal:checkin_start'],
            'event_end' => ['nullable', 'date', 'before_or_equal:checkin_end'],
        ]);

        $station = isset($validated['station_id']) && $validated['station_id']
            ? Station::find($validated['station_id'])
            : null;

        $event = $this->eventService->acceptSuggestion(
            suggestion: $suggestion,
            station: $station,
            acceptedBy: auth()->user(),
            name: $validated['name'],
            hashtag: $validated['hashtag'] ?? null,
            host: $validated['host'] ?? null,
            checkinStart: Carbon::parse($validated['checkin_start']),
            checkinEnd: Carbon::parse($validated['checkin_end']),
            eventStart: isset($validated['event_start']) ? Carbon::parse($validated['event_start']) : null,
            eventEnd: isset($validated['event_end']) ? Carbon::parse($validated['event_end']) : null,
            url: $validated['url'] ?? null,
        );

        return new EventAdminResource($event->load(['station']));
    }

    #[OA\Post(
        path: '/admin/event-suggestions/{id}/deny',
        operationId: 'denyAdminEventSuggestion',
        summary: 'Deny an event suggestion.',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reason'],
                properties: [
                    new OA\Property(property: 'reason', type: 'string', enum: ['denied', 'too-late', 'duplicate', 'not-applicable', 'missing-information']),
                ],
            ),
        ),
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Suggestion denied.'),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function deny(int $id, Request $request): JsonResponse
    {
        $suggestion = EventSuggestion::with(['user'])->findOrFail($id);
        $this->authorize('denySuggestion', Event::class);

        $validated = $request->validate([
            'reason' => ['required', new Enum(EventRejectionReason::class)],
        ]);

        $this->eventService->denySuggestion(
            suggestion: $suggestion,
            reason: EventRejectionReason::from($validated['reason']),
        );

        return response()->json(null, 204);
    }
}
