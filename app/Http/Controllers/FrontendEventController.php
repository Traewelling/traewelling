<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Status;
use App\Http\Controllers\EventController as EventBackend;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class FrontendEventController extends Controller {

    public function index() {
        $events = EventBackend::all();

        return view('admin.event', [
            'upcoming' => $events->filter(function($event) { return $event->begin->isFuture(); }),
            'live' => $events->filter(function($event) { return $event->begin->isPast() && $event->end->isFuture(); }),
            'past' => $events->filter(function($event) { return$event->end->isPast(); }),
        ]);
    }

    public function newForm() {
        return view('admin.eventsForm', ['event' => new Event(), 'isNew' => true]);
    }

    // New Event
    public function store(Request $request) {
        return EventBackend::save($request, new Event());
    }
    // Update existing
    public function update(Request $request, String $slug) {
        $event = Event::where('slug', '=', $slug)->first();
        if($event == null) { abort(404); }

        return EventBackend::save($request, $event);
    }

    public function show(String $slug) {
        $event = EventBackend::getBySlug($slug);

        return view('admin.eventsForm', ['event' => $event, 'isNew' => false]);
    }

    public function destroy(Request $request, String $slug) {
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        $slug = EventBackend::destroy($slug);

        return Redirect::to(route('events.all'))->with('message', $slug . " deleted.");
    }
}
