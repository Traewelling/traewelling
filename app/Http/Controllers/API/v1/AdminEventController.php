<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\EventAdminResource;
use App\Models\Event;
use App\Models\Station;
use App\Repositories\EventRepository;
use App\Services\Event\EventService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminEventRequest',
    title: 'AdminEventRequest',
    required: ['name', 'checkin_start', 'checkin_end'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'hashtag', type: 'string', nullable: true, maxLength: 30),
        new OA\Property(property: 'host', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'url', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'station_id', type: 'integer', nullable: true),
        new OA\Property(property: 'checkin_start', type: 'string', format: 'date'),
        new OA\Property(property: 'checkin_end', type: 'string', format: 'date'),
        new OA\Property(property: 'event_start', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'event_end', type: 'string', format: 'date', nullable: true),
    ],
)]
class AdminEventController extends Controller
{
    private EventService $eventService;

    private EventRepository $eventRepository;

    public function __construct()
    {
        $this->eventService = new EventService();
        $this->eventRepository = new EventRepository();
    }

    #[OA\Get(
        path: '/admin/events',
        operationId: 'getAdminEvents',
        summary: 'List events for admin management.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['future', 'current', 'past'])),
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of events.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/EventAdminResource')),
                ]),
            ),
            new OA\Response(response: 403, description: 'Forbidden.'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Event::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:future,current,past'],
        ]);

        return EventAdminResource::collection(
            $this->eventRepository->paginateForAdminCursor($validated['search'] ?? null, $validated['status'] ?? null),
        );
    }

    #[OA\Get(
        path: '/admin/events/{id}',
        operationId: 'getAdminEvent',
        summary: 'Get a single event for editing.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Event details.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/EventAdminResource'),
                ]),
            ),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
        ],
    )]
    public function show(int $id): EventAdminResource
    {
        $event = Event::with(['station'])->findOrFail($id);
        $this->authorize('update', $event);

        return new EventAdminResource($event);
    }

    #[OA\Post(
        path: '/admin/events',
        operationId: 'createAdminEvent',
        summary: 'Create a new event.',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AdminEventRequest')),
        tags: ['Admin'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Event created.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/EventAdminResource'),
                ]),
            ),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function store(Request $request): EventAdminResource
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate(self::eventValidationRules());

        $station = $this->resolveStation($validated);

        $event = $this->eventService->createEvent(
            name: $validated['name'],
            hashtag: $validated['hashtag'] ?? null,
            host: $validated['host'] ?? null,
            station: $station,
            checkinStart: Carbon::parse($validated['checkin_start']),
            checkinEnd: Carbon::parse($validated['checkin_end']),
            eventStart: isset($validated['event_start']) ? Carbon::parse($validated['event_start']) : null,
            eventEnd: isset($validated['event_end']) ? Carbon::parse($validated['event_end']) : null,
            url: $validated['url'] ?? null,
            acceptedBy: auth()->user(),
        );

        return new EventAdminResource($event->load(['station']));
    }

    #[OA\Put(
        path: '/admin/events/{id}',
        operationId: 'updateAdminEvent',
        summary: 'Update an existing event.',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AdminEventRequest')),
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Event updated.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/EventAdminResource'),
                ]),
            ),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function update(int $id, Request $request): EventAdminResource
    {
        $event = Event::with(['station'])->findOrFail($id);
        $this->authorize('update', $event);

        $validated = $request->validate(self::eventValidationRules());

        $station = $this->resolveStation($validated, $event);

        $this->eventService->updateEvent(
            event: $event,
            name: $validated['name'],
            hashtag: $validated['hashtag'] ?? null,
            host: $validated['host'] ?? null,
            station: $station,
            checkinStart: Carbon::parse($validated['checkin_start']),
            checkinEnd: Carbon::parse($validated['checkin_end']),
            eventStart: isset($validated['event_start']) ? Carbon::parse($validated['event_start']) : null,
            eventEnd: isset($validated['event_end']) ? Carbon::parse($validated['event_end']) : null,
            url: $validated['url'] ?? null,
        );

        return new EventAdminResource($event->fresh(['station']));
    }

    #[OA\Delete(
        path: '/admin/events/{id}',
        operationId: 'deleteAdminEvent',
        summary: 'Delete an event.',
        security: [['passport' => []], ['token' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Deleted.'),
            new OA\Response(response: 403, description: 'Forbidden.'),
            new OA\Response(response: 404, description: 'Not found.'),
        ],
    )]
    public function destroy(int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $this->authorize('delete', $event);
        $event->delete();

        return response()->json(null, 204);
    }

    private static function eventValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'hashtag' => ['nullable', 'string', 'max:30'],
            'host' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'station_id' => ['nullable', 'integer', 'exists:train_stations,id'],
            'checkin_start' => ['required', 'date'],
            'checkin_end' => ['required', 'date', 'after_or_equal:checkin_start'],
            'event_start' => ['nullable', 'date', 'after_or_equal:checkin_start'],
            'event_end' => ['nullable', 'date', 'before_or_equal:checkin_end'],
        ];
    }

    /**
     * Resolves the station from request data.
     * For updates: if station_id is omitted from the request, keep the existing station.
     */
    private function resolveStation(array $validated, ?Event $existing = null): ?Station
    {
        if (array_key_exists('station_id', $validated)) {
            return $validated['station_id'] ? Station::find($validated['station_id']) : null;
        }

        return $existing?->station;
    }
}
