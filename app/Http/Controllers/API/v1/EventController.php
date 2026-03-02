<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\StatusController;
use App\Http\Resources\EventDetailsResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\StatusResource;
use App\Models\Event;
use App\Models\Station;
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
        new OA\Property(property: 'name', type: 'string', maxLength: 255, description: 'name of the event', example: 'Eröffnung der Nebenbahn in Knuffingen'),
        new OA\Property(property: 'host', type: 'string', nullable: true, description: 'host of the event', example: 'MiWuLa'),
        new OA\Property(property: 'begin', type: 'string', format: 'date-time', description: 'Timestamp for the start of the event', example: '2022-06-01T00:00:00+02:00'),
        new OA\Property(property: 'end', type: 'string', format: 'date-time', description: 'Timestamp for the end of the event', example: '2022-08-31T23:59:00+02:00'),
        new OA\Property(property: 'url', type: 'string', maxLength: 255, nullable: true, description: 'external URL for this event', example: 'https://www.example.com/event'),
        new OA\Property(property: 'hashtag', type: 'string', maxLength: 40, nullable: true, description: 'hashtag for this event', example: 'gpn21'),
        new OA\Property(property: 'nearestStation', type: 'string', maxLength: 255, nullable: true, deprecated: true, description: 'Query string for the nearest station. Deprecated: use nearestStationId instead.', example: 'Berlin Hbf'),
        new OA\Property(property: 'nearestStationId', type: 'integer', nullable: true, description: 'ID of the nearest station to this event', example: 1),
    ],
)]
class EventController extends Controller
{
    #[OA\Get(
        path: '/event/{slug}',
        operationId: 'getEvent',
        tags: ['Events'],
        summary: '[Auth optional] Get basic information for event',
        description: 'Returns slug, name and duration for an event',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                description: 'slug for event',
                example: 'weihnachten_2022',
                schema: new OA\Schema(type: 'string'),
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
                            ref: '#/components/schemas/EventResource',
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
        return new EventResource(Event::getBySlug($slug));
    }

    #[OA\Get(
        path: '/event/{slug}/details',
        operationId: 'getEventDetails',
        tags: ['Events'],
        summary: '[Auth optional] Get additional information for event',
        description: 'Returns overall travelled distance and duration for an event',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                description: 'slug for event',
                example: 'weihnachten_2022',
                schema: new OA\Schema(type: 'string'),
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
                            ref: '#/components/schemas/EventDetailsResource',
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
        return new EventDetailsResource(Event::getBySlug($slug));
    }

    #[OA\Get(
        path: '/event/{slug}/statuses',
        operationId: 'getEventStatuses',
        tags: ['Events'],
        summary: '[Auth optional] Get paginated statuses for event',
        description: 'Returns all for user visible statuses for an event',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                description: 'slug for event',
                example: 'weihnachten_2022',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                required: false,
                in: 'query',
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
    public static function statuses(string $slug): AnonymousResourceCollection
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $statuses = StatusController::getStatusesByEvent($event);

        return StatusResource::collection($statuses['statuses']->paginate());
    }

    #[OA\Get(
        path: '/events',
        operationId: 'getEvents',
        tags: ['Events'],
        summary: '[Auth optional] Show active or upcoming events for the given timestamp',
        description: 'Returns all active or upcoming events for the given timestamp. Default timestamp is now. If upcoming is set to true, all events ending after the timestamp are returned.',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        parameters: [
            new OA\Parameter(
                name: 'timestamp',
                in: 'query',
                description: 'The timestamp of view. Default is now.',
                example: '2022-08-01T12:00:00+02:00',
                schema: new OA\Schema(type: 'string'),
                required: false,
            ),
            new OA\Parameter(
                name: 'upcoming',
                in: 'query',
                description: 'Show only upcoming events',
                required: false,
                schema: new OA\Schema(type: 'boolean'),
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
            'upcoming' => ['nullable'],
        ]);

        $validated['timestamp'] = isset($validated['timestamp']) ? Carbon::parse($validated['timestamp']) : now(); // default is now
        $showUpcoming = isset($validated['upcoming']) && $validated['upcoming'] === 'true';

        $events = Event::forTimestamp($validated['timestamp'], $showUpcoming);

        return EventResource::collection($events->simplePaginate());
    }

    #[OA\Post(
        path: '/event',
        operationId: 'suggestEvent',
        tags: ['Events'],
        summary: 'Suggest a event',
        description: 'Submit a possible event for our administrators to publish',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/EventSuggestion'),
        ),
        security: [['passport' => []], ['token' => []]],
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
        $eventSuggestion = EventBackend::suggestEvent(
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
