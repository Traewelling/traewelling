<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\StatusController;
use App\Http\Resources\EventDetailsResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\StatusResource;
use App\Models\Station;
use App\Repositories\EventRepository;
use App\Services\Event\EventService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'EventSuggestion',
    title: 'EventSuggestion',
    description: 'Fields for suggesting an event',
    properties: [
        new OA\Property(property: 'name', description: 'name of the event', type: 'string', example: 'Eröffnung der Nebenbahn in Knuffingen', maxLength: 255),
        new OA\Property(property: 'host', description: 'host of the event', type: 'string', example: 'MiWuLa', nullable: true),
        new OA\Property(property: 'begin', description: 'Timestamp for the start of the event', type: 'string', format: 'date-time', example: '2022-06-01T00:00:00+02:00'),
        new OA\Property(property: 'end', description: 'Timestamp for the end of the event', type: 'string', format: 'date-time', example: '2022-08-31T23:59:00+02:00'),
        new OA\Property(property: 'url', description: 'external URL for this event', type: 'string', example: 'https://www.example.com/event', nullable: true, maxLength: 255),
        new OA\Property(property: 'hashtag', description: 'hashtag for this event', type: 'string', example: 'gpn21', nullable: true, maxLength: 40),
        new OA\Property(property: 'nearestStation', description: 'Query string for the nearest station. Deprecated: use nearestStationId instead.', type: 'string', example: 'Berlin Hbf', nullable: true, deprecated: true, maxLength: 255),
        new OA\Property(property: 'nearestStationId', description: 'ID of the nearest station to this event', type: 'integer', example: 1, nullable: true),
    ],
)]
class EventController extends Controller
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        parent::__construct();
        $this->eventRepository = $eventRepository;
    }

    #[OA\Get(
        path: '/event/{slug}',
        operationId: 'getEvent',
        description: 'Returns slug, name and duration for an event',
        summary: '[Auth optional] Get basic information for event',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Events'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'slug for event',
                in: 'path',
                schema: new OA\Schema(type: 'string'),
                example: 'weihnachten_2022',
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
                            ref: '#/components/schemas/EventResource',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No Event found for this id'),
        ],
    )]
    public function show(string $slug): EventResource
    {
        return new EventResource($this->eventRepository->getBySlug($slug));
    }

    #[OA\Get(
        path: '/event/{slug}/details',
        operationId: 'getEventDetails',
        description: 'Returns overall travelled distance and duration for an event',
        summary: '[Auth optional] Get additional information for event',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Events'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'slug for event',
                in: 'path',
                schema: new OA\Schema(type: 'string'),
                example: 'weihnachten_2022',
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
                            ref: '#/components/schemas/EventDetailsResource',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No Event found for this id'),
        ],
    )]
    public function showDetails(string $slug): EventDetailsResource
    {
        return new EventDetailsResource($this->eventRepository->getBySlug($slug));
    }

    #[OA\Get(
        path: '/event/{slug}/statuses',
        operationId: 'getEventStatuses',
        description: 'Returns all for user visible statuses for an event',
        summary: '[Auth optional] Get paginated statuses for event',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Events'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'slug for event',
                in: 'path',
                schema: new OA\Schema(type: 'string'),
                example: 'weihnachten_2022',
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
            new OA\Response(response: 404, description: 'No Event found for this id'),
        ],
    )]
    public function statuses(string $slug): AnonymousResourceCollection
    {
        $event = $this->eventRepository->getBySlug($slug);
        $statuses = StatusController::getStatusesByEvent($event);

        return StatusResource::collection($statuses['statuses']->paginate());
    }

    #[OA\Get(
        path: '/events',
        operationId: 'getEvents',
        description: 'Returns all active or upcoming events for the given timestamp. Default timestamp is now. If upcoming is set to true, all events ending after the timestamp are returned.',
        summary: '[Auth optional] Show active or upcoming events for the given timestamp',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Events'],
        parameters: [
            new OA\Parameter(
                name: 'timestamp',
                description: 'The timestamp of view. Default is now.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: '2022-08-01T12:00:00+02:00',
            ),
            new OA\Parameter(
                name: 'upcoming',
                description: 'Show only upcoming events (only applicable, if from & to are not used)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean'),
            ),
            new OA\Parameter(
                name: 'from',
                description: 'From date – returns all events in date range (required with "until")',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'date'),
            ),
            new OA\Parameter(
                name: 'until',
                description: 'Until date – returns all events in date range (required with "from")',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'date'),
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
                            items: new OA\Items(ref: '#/components/schemas/EventResource'),
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
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'timestamp' => ['nullable', 'date'],
            'from' => ['nullable', 'date', 'required_with:until'],
            'until' => ['nullable', 'date', 'after:from', 'required_with:until'],
            'upcoming' => ['nullable'],
        ]);

        if (isset($validated['from']) && isset($validated['until'])) {
            return $this->eventRepository->paginateForPeriod(Carbon::parse($validated['from']), Carbon::parse($validated['until']));
        }

        $validated['timestamp'] = isset($validated['timestamp']) ? Carbon::parse($validated['timestamp']) : now(); // default is now
        $showUpcoming = isset($validated['upcoming']) && $validated['upcoming'] === 'true';

        return $this->eventRepository->paginateForTimestamp($validated['timestamp'], $showUpcoming);
    }

    #[OA\Post(
        path: '/event',
        operationId: 'suggestEvent',
        description: 'Submit a possible event for our administrators to publish',
        summary: 'Suggest a event',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/EventSuggestion'),
        ),
        tags: ['Events'],
        responses: [
            new OA\Response(response: 201, description: 'successful operation'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'User not authorized'),
        ],
    )]
    public function suggest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'host' => ['nullable', 'string', 'max:255'],
            'begin' => ['required', 'date'],
            'end' => ['required', 'date'],
            'url' => ['nullable', 'url', 'max:255'],
            'hashtag' => ['nullable', 'string', 'max:40'],
            'nearestStation' => ['nullable', 'string', 'max:255'],
            'nearestStationId' => ['nullable', 'integer', 'exists:train_stations,id'],
        ]);

        $nearestStation = null;
        if (isset($validated['nearestStationId'])) {
            $nearestStation = Station::find($validated['nearestStationId']);
        } elseif (isset($validated['nearestStation'])) {
            $stations = $this->dataProvider->getStations($validated['nearestStation'], 1);
            if (count($stations) === 0) {
                return $this->sendError(error: __('events.request.station_not_found'), code: 400);
            }
            $nearestStation = $stations->first();
        }
        $eventSuggestion = (new EventService())->suggestEvent(
            user: auth()->user(),
            name: $validated['name'],
            begin: Carbon::parse($validated['begin']),
            end: Carbon::parse($validated['end']),
            station: $nearestStation ?? null,
            url: $validated['url'] ?? null,
            host: $validated['host'] ?? null,
            hashtag: $validated['hashtag'] ?? null,
        );

        if ($eventSuggestion->wasRecentlyCreated) {
            return $this->sendResponse(data: ['message' => __('events.request.success')], code: 201);
        }

        return $this->sendError(error: __('messages.exception.general'), code: 500);
    }
}
