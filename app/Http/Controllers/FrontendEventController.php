<?php

namespace App\Http\Controllers;

use App\Http\Controllers\EventController as EventBackend;
use App\Models\Event;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FrontendEventController extends Controller
{

    public function newForm(): Renderable {
        return view('admin.eventsForm', ['event' => new Event(), 'isNew' => true]);
    }

    // New Event
    public function store(Request $request): RedirectResponse {
        return EventBackend::save($request, new Event());
    }

    // Update existing
    public function update(Request $request, string $slug): RedirectResponse {
        $event = Event::where('slug', '=', $slug)->first();
        if ($event == null) {
            abort(404);
        }

        return EventBackend::save($request, $event);
    }

    public function show(string $slug): Renderable {
        $event = EventBackend::getBySlug($slug);

        return view('admin.eventsForm', ['event' => $event, 'isNew' => false]);
    }

    public function destroy(Request $request, string $slug): RedirectResponse {
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        $slug = EventBackend::destroy($slug);

        return redirect()->route('events.all')
                         ->with('message', $slug . " deleted.");
    }
}
