<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Exceptions\HafasException;
use App\Http\Controllers\Backend\Admin\EventController as AdminEventBackend;
use App\Http\Controllers\Backend\Admin\TelegramController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HafasController;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Notifications\EventSuggestionProcessed;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class EventController extends Controller
{

    private const VALIDATOR_RULES = [
        'name'                 => ['required', 'max:255'],
        'hashtag'              => ['required', 'max:30'],
        'host'                 => ['required', 'max:255'],
        'url'                  => ['nullable', 'url'],
        'nearest_station_name' => ['required', 'max:255'],
        'begin'                => ['required', 'date'],
        'end'                  => ['required', 'date'],
        'event_start'          => ['nullable', 'date', 'after_or_equal:begin'],
        'event_end'            => ['nullable', 'date', 'before_or_equal:end'],
    ];

    public function renderList(Request $request): View {
        $events = Event::orderByDesc('end');
        if ($request->has('query')) {
            $events->where('name', 'LIKE', '%' . strip_tags($request->get('query')) . '%');
        }
        return view('admin.events.list', [
            'events' => $events->paginate(10)
        ]);
    }

    public function renderSuggestions(): View {
        return view('admin.events.suggestions', [
            'suggestions' => EventSuggestion::where('processed', false)
                                            ->where('end', '>', DB::raw('CURRENT_TIMESTAMP'))
                                            ->orderBy('begin')
                                            ->get()
        ]);
    }

    public function renderSuggestionCreation(int $id): View {
        return view('admin.events.suggestion-create', [
            'event' => EventSuggestion::findOrFail($id)
        ]);
    }

    public function renderCreate(): View {
        return view('admin.events.create');
    }

    public function renderEdit(int $id): View {
        return view('admin.events.edit', ['event' => Event::findOrFail($id)]);
    }

    public function denySuggestion(Request $request): RedirectResponse {
        $validated       = $request->validate(['id' => ['required', 'exists:event_suggestions,id']]);
        $eventSuggestion = EventSuggestion::find($validated['id']);
        $eventSuggestion->update(['processed' => true]);
        if (!App::runningUnitTests() && config('app.admin.webhooks.new_event') !== null) {
            Http::post(config('app.admin.webhooks.new_event'), [
                auth()->user()->name . ' denied the event "' . $eventSuggestion->name . '".'
            ]);
        }
        $eventSuggestion->user->notify(new EventSuggestionProcessed($eventSuggestion, null));

        return back()->with('alert-success', 'Event denied.');
    }

    /**
     * @throws HafasException
     */
    public function acceptSuggestion(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'suggestionId'         => ['required', 'exists:event_suggestions,id'],
                                            'name'                 => ['required', 'max:255'],
                                            'hashtag'              => ['required', 'max:30'],
                                            'host'                 => ['required', 'max:255'],
                                            'url'                  => ['nullable', 'url'],
                                            'nearest_station_name' => ['nullable', 'max:255'],
                                            'begin'                => ['required', 'date'],
                                            'end'                  => ['required', 'date'],
                                            'event_start'          => ['nullable', 'date', 'after_or_equal:begin'],
                                            'event_end'            => ['nullable', 'date', 'before_or_equal:end'],
                                        ]);

        $eventSuggestion = EventSuggestion::find($validated['suggestionId']);
        $trainStation    = null;

        if (isset($validated['nearest_station_name'])) {
            $trainStation = HafasController::getStations($validated['nearest_station_name'], 1)->first();

            if ($trainStation === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
        }

        $event = Event::create([
                                   'name'        => $validated['name'],
                                   'slug'        => AdminEventBackend::createSlugFromName($validated['name']),
                                   'hashtag'     => $validated['hashtag'],
                                   'host'        => $validated['host'],
                                   'station_id'  => $trainStation?->id,
                                   'begin'       => Carbon::parse($validated['begin'])->toIso8601String(),
                                   'end'         => Carbon::parse($validated['end'])->toIso8601String(),
                                   'event_start' => Carbon::parse($validated['event_start'])->toIso8601String(),
                                   'event_end'   => Carbon::parse($validated['event_end'])->toIso8601String(),
                                   'url'         => $validated['url'] ?? null,
                                   'accepted_by' => auth()->user()->id,
                               ]);

        $eventSuggestion->update(['processed' => true]);
        if (!App::runningUnitTests() && config('app.admin.webhooks.new_event') !== null) {
            Http::post(config('app.admin.webhooks.new_event'), [
                'content' => auth()->user()->name . ' accepted the event "' . $eventSuggestion->name . '".',
            ]);
        }

        $eventSuggestion->user->notify(new EventSuggestionProcessed($eventSuggestion, $event));

        return redirect()->route('admin.events')->with('alert-success', 'Das Event wurde akzeptiert!');
    }

    /**
     * @throws HafasException
     */
    public function create(Request $request): RedirectResponse {
        $validated = $request->validate(self::VALIDATOR_RULES);

        $trainStation = null;
        if (isset($validated['nearest_station_name'])) {
            $trainStation = HafasController::getStations($validated['nearest_station_name'], 1)->first();

            if ($trainStation === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
        }

        Event::create([
                          'name'        => $validated['name'],
                          'slug'        => AdminEventBackend::createSlugFromName($validated['name']),
                          'hashtag'     => $validated['hashtag'],
                          'host'        => $validated['host'],
                          'station_id'  => $trainStation?->id,
                          'begin'       => Carbon::parse($validated['begin'])->toIso8601String(),
                          'end'         => Carbon::parse($validated['end'])->toIso8601String(),
                          'event_start' => Carbon::parse($validated['event_start'])->toIso8601String(),
                          'event_end'   => Carbon::parse($validated['event_end'])->toIso8601String(),
                          'url'         => $validated['url'] ?? null,
                          'accepted_by' => auth()->user()->id
                      ]);

        return redirect()->route('admin.events')->with('alert-success', 'The event was created!');
    }

    public function edit(int $id, Request $request): RedirectResponse {
        $validated = $request->validate(self::VALIDATOR_RULES);

        $event = Event::findOrFail($id);

        $trainStation = HafasController::getStations($validated['nearest_station_name'], 1)->first();

        if ($trainStation === null) {
            return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
        }
        $validated['station_id'] = $trainStation->id;

        $event->update($validated);

        return redirect()->route('admin.events')->with('alert-success', 'Das Event wurde bearbeitet!');
    }

    public function deleteEvent(Request $request): RedirectResponse {
        $validated = $request->validate(['id' => ['required', 'exists:events,id']]);
        $event     = Event::find($validated['id']);
        $event->delete();
        return redirect()->route('admin.events')->with('alert-success', 'Das Event wurde gelöscht!');
    }
}
