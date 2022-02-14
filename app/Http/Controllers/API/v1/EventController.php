<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\PermissionException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Resources\EventResource;
use App\Http\Resources\StatusResource;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Intervention\Image\Commands\ResponseCommand;

class EventController extends ResponseController
{
    /**
     * Returns model of Event
     * @param string $slug
     * @return EventResource
     */
    public function show(string $slug): EventResource {
        $event = EventBackend::getBySlug($slug);
        return new EventResource($event);
    }

    /**
     * Returns paginated statuses for user
     * @param string $slug
     * @return AnonymousResourceCollection
     */
    public static function statuses(string $slug): AnonymousResourceCollection {
        $event = EventBackend::getBySlug($slug);
        return StatusResource::collection($event->statuses()->paginate(15));
    }

    /**
     * Returns upcoming events
     */
    public function upcoming(): AnonymousResourceCollection {
        $events = EventBackend::getUpcomingEvents();
        return EventResource::collection($events);
    }

    /**
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
        return $this->sendError(error: null, code: 500);
    }

    public function activeEvents(): AnonymousResourceCollection {
        $events = EventBackend::activeEvents();
        return EventResource::collection($events);
    }
}
