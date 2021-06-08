<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\Admin\TelegramController;
use App\Http\Controllers\Backend\EventController as EventBackend;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    public function suggestEvent(Request $request): RedirectResponse {
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
            return back()->with('success', __('events.request.success'));
        }
        return back()->with('error', __('messages.exception.general'));
    }
}
