<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Resources\EventResource;
use App\Http\Resources\StatusResource;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class EventController extends Controller
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
    public static function upcoming() {
        $events = EventBackend::getUpcomingEvents();
        return EventResource::collection($events);
    }
}
