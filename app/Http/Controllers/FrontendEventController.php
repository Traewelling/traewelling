<?php

namespace App\Http\Controllers;

use App\Event;
use App\Status;
use App\Http\Controllers\EventController as EventBackend;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class FrontendEventController extends Controller {

    public function index() {
        $events = EventBackend::all();

        $t = time();

        return view('event', [
            'upcoming' => $events->filter(function($e) use ($t) { return $t < strtotime($e->begin); }),
            'live' => $events->filter(function($e) use ($t) { return strtotime($e->begin) <= $t && strtotime($e->end) >= $t; }),
            'past' => $events->filter(function($e) use ($t) { return $t > strtotime($e->end); }),
        ]);
    }

    public function newForm() {
        return view('eventsForm', ['event' => new Event(), 'isNew' => true]);
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

        return view('eventsForm', ['event' => $event, 'isNew' => false]);
    }

    public function destroy(Request $request, String $slug) {
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        $slug = EventBackend::destroy($slug);

        return Redirect::to(route('events.all'))->with('message', $slug . " deleted.");
    }
}
