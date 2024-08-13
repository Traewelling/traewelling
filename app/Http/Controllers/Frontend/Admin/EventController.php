<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Enum\EventRejectionReason;
use App\Exceptions\HafasException;
use App\Http\Controllers\Backend\Admin\EventController as AdminEventBackend;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HafasController;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Notifications\EventSuggestionProcessed;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class EventController extends Controller
{

    private const VALIDATOR_RULES = [
        'name'                 => ['required', 'max:255'],
        'hashtag'              => ['nullable', 'max:30'],
        'host'                 => ['nullable', 'max:255'],
        'url'                  => ['nullable', 'url'],
        'nearest_station_name' => ['nullable', 'max:255'],
        'checkin_start'        => ['required', 'date'],
        'checkin_end'          => ['required', 'date'],
        'event_start'          => ['nullable', 'date', 'after_or_equal:checkin_start'],
        'event_end'            => ['nullable', 'date', 'before_or_equal:checkin_end'],
    ];

    public function renderList(Request $request): View {
        $queryBase = Event::query();
        if ($request->has('query')) {
            $queryBase->where('name', 'LIKE', '%' . strip_tags($request->get('query')) . '%');
        }

        return view('admin.events.list', [
            'events_future'  => $queryBase->clone()
                                          ->orderBy('checkin_start')
                                          ->whereDate('checkin_start', '>', DB::raw('now()'))
                                          ->paginate(10, pageName: 'future'),
            'events_current' => $queryBase->clone()
                                          ->orderBy('checkin_start')
                                          ->where('checkin_start', '<', DB::raw('now()'))
                                          ->where('checkin_end', '>', DB::raw('now()'))
                                          ->paginate(10, pageName: 'current'),
            'events_past'    => $queryBase->clone()
                                          ->where('checkin_end', '<', DB::raw('now()'))
                                          ->paginate(10, pageName: 'past')
        ]);
    }

    public function renderSuggestions(): View {
        return view('admin.events.suggestions', [
            'suggestions' => EventSuggestion::where('processed', false)
                                            ->where(DB::raw('DATE(end)'), '>=', DB::raw('DATE(CURRENT_TIMESTAMP)'))
                                            ->orderBy('begin')
                                            ->get()
        ]);
    }

    public function renderSuggestionCreation(int $id): View {
        $suggestion     = EventSuggestion::findOrFail($id);
        $parallelEvents = Event::where([
                                           [DB::raw('DATE(checkin_start)'), '>=', $suggestion->end->toDateString()],
                                           [DB::raw('DATE(checkin_end)'), '<=', $suggestion->begin->toDateString()]
                                       ])
                               ->orWhere([
                                             [DB::raw('DATE(checkin_end)'), '>=', $suggestion->begin->toDateString()],
                                             [DB::raw('DATE(checkin_end)'), '<=', $suggestion->end->toDateString()]
                                         ])
                               ->orWhere([
                                             [DB::raw('DATE(checkin_start)'), '>=', $suggestion->begin->toDateString()],
                                             [DB::raw('DATE(checkin_start)'), '<=', $suggestion->end->toDateString()]
                                         ])
                               ->get();

        $parallelEvents->map(function($event) use ($suggestion) {
            similar_text($event->name, $suggestion->name, $perc);
            $event->similarity = $perc;

            return $event;
        });

        return view('admin.events.suggestion-create', [
            'eventSuggestion' => $suggestion,
            'parallelEvents'  => $parallelEvents->sortByDesc('similarity')
        ]);
    }

    public function renderEdit(int $id): View {
        return view('admin.events.form', ['event' => Event::findOrFail($id)]);
    }

    public function denySuggestion(Request $request): RedirectResponse {
        $validated       = $request->validate([
                                                  'id'              => ['required', 'exists:event_suggestions,id'],
                                                  'rejectionReason' => [
                                                      'required', new Enum(EventRejectionReason::class)
                                                  ]
                                              ]);
        $eventSuggestion = EventSuggestion::find($validated['id']);
        $eventSuggestion->update(['processed' => true]);

        if ($eventSuggestion->admin_notification_id !== null) {
            TelegramService::admin()->deleteMessage($eventSuggestion->admin_notification_id);
        }

        $eventSuggestion->user->notify(
            new EventSuggestionProcessed(
                $eventSuggestion,
                null,
                EventRejectionReason::from($validated['rejectionReason'])
            )
        );

        return redirect()->route('admin.events.suggestions')->with('alert-success', 'Event denied.');
    }

    /**
     * @throws HafasException
     */
    public function acceptSuggestion(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'suggestionId'         => ['required', 'exists:event_suggestions,id'],
                                            'name'                 => ['required', 'max:255'],
                                            'hashtag'              => ['nullable', 'max:30'],
                                            'host'                 => ['nullable', 'max:255'],
                                            'url'                  => ['nullable', 'url'],
                                            'nearest_station_name' => ['nullable', 'max:255'],
                                            'begin'                => ['required', 'date'],
                                            'end'                  => ['required', 'date'],
                                            'event_start'          => ['nullable', 'date', 'after_or_equal:begin'],
                                            'event_end'            => ['nullable', 'date', 'before_or_equal:end'],
                                        ]);

        $eventSuggestion = EventSuggestion::find($validated['suggestionId']);
        $station         = null;

        if ($eventSuggestion->user_id === auth()->user()->id && !auth()->user()?->hasRole('admin')) {
            return back()->with('alert-danger', 'You can\'t accept your own suggestion.');
        }

        if (isset($validated['nearest_station_name'])) {
            $station = HafasController::getStations($validated['nearest_station_name'], 1)->first();

            if ($station === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
        }

        $event = Event::create([
                                   'name'          => $validated['name'],
                                   'slug'          => AdminEventBackend::createSlugFromName($validated['name']),
                                   'hashtag'       => $validated['hashtag'],
                                   'host'          => $validated['host'],
                                   'station_id'    => $station?->id,
                                   'checkin_start' => Carbon::parse($validated['begin'])->toIso8601String(),
                                   'checkin_end'   => Carbon::parse($validated['end'])->toIso8601String(),
                                   'event_start'   => Carbon::parse($validated['event_start'] ?? $validated['begin'])->toIso8601String(),
                                   'event_end'     => Carbon::parse($validated['event_end'] ?? $validated['end'])->toIso8601String(),
                                   'url'           => $validated['url'] ?? null,
                                   'accepted_by'   => auth()->user()->id,
                               ]);

        $eventSuggestion->update(['processed' => true]);

        if ($eventSuggestion->admin_notification_id !== null) {
            TelegramService::admin()->deleteMessage($eventSuggestion->admin_notification_id);
        }

        $eventSuggestion->user->notify(new EventSuggestionProcessed($eventSuggestion, $event));

        return redirect()->route('admin.events.suggestions')->with('alert-success', 'Das Event wurde akzeptiert!');
    }

    /**
     * @throws HafasException
     */
    public function create(Request $request): RedirectResponse {
        $validated = $request->validate(self::VALIDATOR_RULES);

        $station = null;
        if (isset($validated['nearest_station_name'])) {
            $station = HafasController::getStations($validated['nearest_station_name'], 1)->first();

            if ($station === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
        }

        $validated['slug']          = AdminEventBackend::createSlugFromName($validated['name']);
        $validated['station_id']    = $station?->id;
        $validated['checkin_start'] = Carbon::parse($validated['checkin_start'])->toIso8601String();
        $validated['checkin_end']   = Carbon::parse($validated['checkin_end'])->toIso8601String();
        if (isset($validated['event_start'])) {
            $validated['event_start'] = Carbon::parse($validated['event_start'])->toIso8601String();
        }
        if (isset($validated['event_end'])) {
            $validated['event_end'] = Carbon::parse($validated['event_end'])->toIso8601String();
        }
        $validated['accepted_by'] = auth()->user()->id;

        Event::create($validated);

        return redirect()->route('admin.events')->with('alert-success', 'The event was created!');
    }

    public function edit(int $id, Request $request): RedirectResponse {
        $validated = $request->validate(self::VALIDATOR_RULES);

        $event = Event::findOrFail($id);

        if (strlen($validated['nearest_station_name'] ?? '') === 0) {
            $validated['station_id'] = null;
        } elseif ($validated['nearest_station_name'] && $validated['nearest_station_name'] !== $event->station->name) {
            $station = HafasController::getStations($validated['nearest_station_name'], 1)->first();

            if ($station === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
            $validated['station_id'] = $station->id;
        }

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
