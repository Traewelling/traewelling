<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Enum\EventRejectionReason;
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
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class EventController extends Controller
{

    private const VALIDATOR_RULES = [
        'name'                 => ['required', 'max:255'],
        'hashtag'              => ['nullable', 'max:30'],
        'host'                 => ['nullable', 'max:255'],
        'url'                  => ['nullable', 'url'],
        'nearest_station_name' => ['nullable', 'max:255'],
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
                                            ->where('user_id', '!=', auth()->user()->id) //moderator can't accept their own suggestions
                                            ->orderBy('begin')
                                            ->get()
        ]);
    }

    public function renderSuggestionCreation(int $id): View {
        return view('admin.events.suggestion-create', [
            'event' => EventSuggestion::findOrFail($id)
        ]);
    }

    public function renderEdit(int $id): View {
        return view('admin.events.edit', ['event' => Event::findOrFail($id)]);
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
        if (!App::runningUnitTests() && config('app.admin.notification.url') !== null) {
            Http::post(config('app.admin.notification.url'), [
                'chat_id'    => config('app.admin.notification.chat_id'),
                'text'       => strtr("<b>Event suggestion denied</b>" . PHP_EOL .
                                      "Title: :name" . PHP_EOL
                                      . "Denial reason: :reason" . PHP_EOL
                                      . "Denial user: :username" . PHP_EOL, [
                                          ':name'     => $eventSuggestion->name,
                                          ':reason'   => EventRejectionReason::from($validated['rejectionReason'])->getReason(),
                                          ':username' => auth()->user()->username,
                                      ]),
                'parse_mode' => 'HTML',
            ]);
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
        $trainStation    = null;

        if ($eventSuggestion->user_id === auth()->user()->id && !auth()->user()?->hasRole('admin')) {
            return back()->with('alert-danger', 'You can\'t accept your own suggestion.');
        }

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
                                   'event_start' => Carbon::parse($validated['event_start'] ?? $validated['begin'])->toIso8601String(),
                                   'event_end'   => Carbon::parse($validated['event_end'] ?? $validated['end'])->toIso8601String(),
                                   'url'         => $validated['url'] ?? null,
                                   'accepted_by' => auth()->user()->id,
                               ]);

        $eventSuggestion->update(['processed' => true]);
        if (!App::runningUnitTests() && config('app.admin.notification.url') !== null) {
            Http::post(config('app.admin.notification.url'), [
                'chat_id'    => config('app.admin.notification.chat_id'),
                'text'       => strtr("<b>Event suggestion accepted</b>" . PHP_EOL .
                                      "Title: :name" . PHP_EOL
                                      . "Accepting user: :username" . PHP_EOL, [
                                          ':name'     => $eventSuggestion->name,
                                          ':username' => auth()->user()->username,
                                      ]),
                'parse_mode' => 'HTML',
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
                          'event_start' => Carbon::parse($validated['event_start'] ?? $validated['begin'])->toIso8601String(),
                          'event_end'   => Carbon::parse($validated['event_end'] ?? $validated['end'])->toIso8601String(),
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

    public function renderHistory(int $eventId): View {
        $this->authorize('view event history');

        $activities = Activity::where('subject_type', Event::class)
                              ->where('subject_id', $eventId)
                              ->orderByDesc('created_at')
                              ->paginate(10);

        return view('admin.events.history', [
            'eventId'    => $eventId,
            'activities' => $activities,
        ]);
    }
}
