<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\StatusController;
use App\Http\Resources\EventDetailsResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\StatusResource;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    /**
     * @OA\Get(
     *      path="/event/{slug}",
     *      operationId="getEvent",
     *      tags={"Events"},
     *      summary="[Auth optional] Get basic information for event",
     *      description="Returns slug, name and duration for an event",
     *      @OA\Parameter (
     *          name="slug",
     *          in="path",
     *          description="slug for event",
     *          example="weihnachten_2022",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property (
     *                  property="data",
     *                  type="object",
     *                      ref="#/components/schemas/EventResource"
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     *
     *
     *
     * Returns model of Event
     *
     * @param string $slug
     *
     * @return EventResource
     */
    public function show(string $slug): EventResource {
        return new EventResource(Event::getBySlug($slug));
    }

    /**
     * @OA\Get(
     *      path="/event/{slug}/details",
     *      operationId="getEventDetails",
     *      tags={"Events"},
     *      summary="[Auth optional] Get additional information for event",
     *      description="Returns overall travelled distance and duration for an event",
     *      @OA\Parameter (
     *          name="slug",
     *          in="path",
     *          description="slug for event",
     *          example="weihnachten_2022",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property (
     *                  property="data",
     *                  type="object",
     *                      ref="#/components/schemas/EventDetailsResource"
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     *
     *
     * Returns stats for event
     *
     * @param string $slug
     *
     * @return EventDetailsResource
     */
    public function showDetails(string $slug): EventDetailsResource {
        return new EventDetailsResource(Event::getBySlug($slug));
    }

    /**
     * @OA\Get(
     *      path="/event/{slug}/statuses",
     *      operationId="getEventStatuses",
     *      tags={"Events"},
     *      summary="[Auth optional] Get paginated statuses for event",
     *      description="Returns all for user visible statuses for an event",
     *      @OA\Parameter (
     *          name="slug",
     *          in="path",
     *          description="slug for event",
     *          example="weihnachten_2022",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter (
     *          name="page",
     *          description="Page of pagination",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/StatusResource"
     *                  )
     *              ),
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     *
     * Returns paginated statuses for user
     *
     * @param string $slug
     *
     * @return AnonymousResourceCollection
     */
    public static function statuses(string $slug): AnonymousResourceCollection {
        $event    = Event::where('slug', $slug)->firstOrFail();
        $statuses = StatusController::getStatusesByEvent($event);
        return StatusResource::collection($statuses['statuses']->paginate());
    }

    /**
     * @OA\Get(
     *      path="/events",
     *      operationId="getEvents",
     *      tags={"Events"},
     *      summary="[Auth optional] Show active or upcoming events for the given timestamp",
     *      description="Returns all active or upcoming events for the given timestamp. Default timestamp is now. If upcoming is set to true, all events ending after the timestamp are returned.",
     *      @OA\Parameter (
     *          name="timestamp",
     *          in="query",
     *          description="The timestamp of view. Default is now.",
     *          example="2022-08-01T12:00:00+02:00",
     *          @OA\Schema(type="string"),
     *          required=false
     *      ),
     *      @OA\Parameter (
     *          name="upcoming",
     *          in="query",
     *          description="Show only upcoming events",
     *          required=false,
     *          @OA\Schema(type="boolean")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property (
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/EventResource"
     *                  )
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     */
    public function index(Request $request): AnonymousResourceCollection {
        $validated = $request->validate([
                                            'timestamp' => ['nullable', 'date'],
                                            'upcoming'  => ['nullable'],
                                        ]);

        $validated['timestamp'] = isset($validated['timestamp']) ? Carbon::parse($validated['timestamp']) : now(); //default is now
        $showUpcoming           = isset($validated['upcoming']) && $validated['upcoming'] === 'true';

        $events = Event::forTimestamp($validated['timestamp'], $showUpcoming);
        return EventResource::collection($events->simplePaginate());
    }

    /**
     * @OA\Post(
     *      path="/event",
     *      operationId="suggestEvent",
     *      tags={"Events"},
     *      summary="Suggest a event",
     *      description="Submit a possible event for our administrators to publish",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/EventSuggestion")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=403, description="User not authorized"),
     *       security={
     *           {"passport": {}}, {"token": {}}
     *
     *       }
     *     )
     *
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function suggest(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'name'           => ['required', 'string', 'max:255'],
                                            'host'           => ['nullable', 'string', 'max:255'],
                                            'begin'          => ['required', 'date'],
                                            'end'            => ['required', 'date'],
                                            'url'            => ['nullable', 'url', 'max:255'],
                                            'hashtag'        => ['nullable', 'string', 'max:40'],
                                            'nearestStation' => ['nullable', 'string', 'max:255'],
                                        ]);

        if (isset($validated['nearestStation'])) {
            $stations = $this->dataProvider->getStations($validated['nearestStation'], 1);
            if (count($stations) === 0) {
                return $this->sendError(error: __('events.request.station_not_found'), code: 400);
            }
            $nearestStation = $stations->first();
        }
        $eventSuggestion = EventBackend::suggestEvent(
            user:    auth()->user(),
            name:    $validated['name'],
            begin:   Carbon::parse($validated['begin']),
            end:     Carbon::parse($validated['end']),
            station: $nearestStation ?? null,
            url:     $validated['url'] ?? null,
            host:    $validated['host'] ?? null,
            hashtag: $validated['hashtag'] ?? null,
        );

        if ($eventSuggestion->wasRecentlyCreated) {
            return $this->sendResponse(data: ['message' => __('events.request.success')], code: 201);
        }
        return $this->sendError(error: __('messages.exception.general'), code: 500);
    }
}
