<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\EventRepository;
use Illuminate\View\View;

class EventController extends Controller
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function renderEventOverview(): View
    {
        $events = $this->eventRepository->paginateForFrontend(timestamp: now());

        return view('events.overview', [
            'liveAndUpcomingEvents' => $events,
        ]);
    }
}
