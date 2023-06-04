<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;

class EventController extends Controller
{
    public function renderEventOverview(): Renderable {
        $liveAndUpcomingEvents = Event::where('end', '>=', Carbon::now()->toIso8601String())
                                      ->orderBy('begin')
                                      ->paginate(15);
        return view('events.overview', [
            'liveAndUpcomingEvents' => $liveAndUpcomingEvents
        ]);
    }
}
