<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventDetailsResource;
use App\Http\Resources\StatusResource;
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
     *                      ref="#/components/schemas/Event"
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"token": {}},
     *           {}
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
        $event = EventBackend::getBySlug($slug);
        return new EventResource($event);
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
     *                      ref="#/components/schemas/EventDetails"
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"token": {}},
     *           {}
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
        $event = EventBackend::getBySlug($slug);
        return new EventDetailsResource($event);
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
     *                      ref="#/components/schemas/Status"
     *                  )
     *              ),
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"token": {}},
     *           {}
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
        $event = EventBackend::getBySlug($slug);
        return StatusResource::collection($event->statuses()->paginate(15));
    }

    /**
     *  @OA\Get(
     *      path="/events",
     *      operationId="getUpcomingEvent",
     *      tags={"Events"},
     *      summary="[Auth optional] Shows upcoming events with basic information",
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
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Event"
     *                  )
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     * Returns upcoming events
     */
    public function upcoming(): AnonymousResourceCollection {
        $events = EventBackend::getUpcomingEvents();
        return EventResource::collection($events);
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
     *           {"token": {}},
     *           {}
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
                                            'name'  => ['required', 'max:255'],
                                            'host'  => ['nullable', 'max:255'],
                                            'begin' => ['required', 'date'],
                                            'end'   => ['required', 'date'],
                                            'url'   => ['nullable', 'max:255'],
                                        ]);

        $eventSuggestion = EventBackend::suggestEvent(
            user: auth()->user(),
            name: $validated['name'],
            begin: Carbon::parse($validated['begin']),
            end: Carbon::parse($validated['end']),
            url: $validated['url'] ?? null,
            host: $validated['host'] ?? null
        );

        if ($eventSuggestion->wasRecentlyCreated) {
            return $this->sendv1Response(data: null, code: 201);
        }
        return $this->sendv1Error(error: null, code: 500);
    }

    /**
     *   @OA\Get(
     *      path="/activeEvents",
     *      operationId="getCurrentEvents",
     *      tags={"Events"},
     *      summary="Shows current events with basic information",
     *      description="Returns array of current events, used for a basic overview during checkiused for a basic overview during checkinn",
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
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Event"
     *                  )
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     * @return AnonymousResourceCollection
     */
    public function activeEvents(): AnonymousResourceCollection {
        $events = EventBackend::activeEvents();
        return EventResource::collection($events);
    }
}
