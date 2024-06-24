<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function renderEventOverview(): View {
        $events = Event::forTimestamp(
            timestamp:    now(),
            showUpcoming: true
        )
                       ->with(['station'])
                       ->paginate(15);
        return view('events.overview', [
            'liveAndUpcomingEvents' => $events
        ]);
    }
}
