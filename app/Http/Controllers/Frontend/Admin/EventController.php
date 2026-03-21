<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend\Admin;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\Enum\EventRejectionReason;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Repositories\EventRepository;
use App\Services\Event\EventService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class EventController extends Controller
{
    private DataProviderInterface $dataProvider;

    private EventService $eventService;

    private EventRepository $eventRepository;

    public function __construct()
    {
        $this->dataProvider = new DataProviderBuilder()->build();
        $this->eventService = new EventService();
        $this->eventRepository = new EventRepository();
    }

    private const VALIDATOR_RULES = [
        'name' => ['required', 'max:255'],
        'hashtag' => ['nullable', 'max:30'],
        'host' => ['nullable', 'max:255'],
        'url' => ['nullable', 'url'],
        'nearest_station_name' => ['nullable', 'max:255'],
        'checkin_start' => ['required', 'date'],
        'checkin_end' => ['required', 'date'],
        'event_start' => ['nullable', 'date', 'after_or_equal:checkin_start'],
        'event_end' => ['nullable', 'date', 'before_or_equal:checkin_end'],
    ];

    public function index(Request $request): View
    {
        $paginated = $this->eventRepository->paginateForAdmin($request->get('query'));

        return view('admin.events.index', [
            'events_future' => $paginated['future'],
            'events_current' => $paginated['current'],
            'events_past' => $paginated['past'],
        ]);
    }

    public function renderSuggestions(): View
    {
        return view('admin.events.suggestions', [
            'suggestions' => EventSuggestion::where('processed', false)
                ->where('end', '>=', today()->toDateString())
                ->orderBy('begin')
                ->get(),
        ]);
    }

    public function renderSuggestionCreation(int $id): View
    {
        $suggestion = EventSuggestion::findOrFail($id);

        return view('admin.events.suggestion-create', [
            'eventSuggestion' => $suggestion,
            'parallelEvents' => $this->eventRepository->findParallelEventsWithSimilarity($suggestion),
        ]);
    }

    public function renderEdit(int $id): View
    {
        return view('admin.events.form', ['event' => Event::findOrFail($id)]);
    }

    public function denySuggestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'exists:event_suggestions,id'],
            'rejectionReason' => ['required', new Enum(EventRejectionReason::class)],
        ]);

        $this->eventService->denySuggestion(
            suggestion: EventSuggestion::findOrFail($validated['id']),
            reason: EventRejectionReason::from($validated['rejectionReason']),
        );

        return redirect()->route('admin.events.suggestions')->with('alert-success', 'Event denied.');
    }

    public function acceptSuggestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'suggestionId' => ['required', 'exists:event_suggestions,id'],
            'name' => ['required', 'max:255'],
            'hashtag' => ['nullable', 'max:30'],
            'host' => ['nullable', 'max:255'],
            'url' => ['nullable', 'url'],
            'nearest_station_name' => ['nullable', 'max:255'],
            'begin' => ['required', 'date'],
            'end' => ['required', 'date'],
            'event_start' => ['nullable', 'date', 'after_or_equal:begin'],
            'event_end' => ['nullable', 'date', 'before_or_equal:end'],
        ]);

        $suggestion = EventSuggestion::findOrFail($validated['suggestionId']);

        if ($suggestion->user_id === auth()->id() && !auth()->user()?->hasRole('admin')) {
            return back()->with('alert-danger', 'You can\'t accept your own suggestion.');
        }

        $station = null;
        if (isset($validated['nearest_station_name'])) {
            $station = $this->dataProvider->getStations($validated['nearest_station_name'], 1)->first();
            if ($station === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
        }

        $this->eventService->acceptSuggestion(
            suggestion: $suggestion,
            station: $station,
            acceptedBy: auth()->user(),
            name: $validated['name'],
            hashtag: $validated['hashtag'] ?? null,
            host: $validated['host'] ?? null,
            checkinStart: Carbon::parse($validated['begin']),
            checkinEnd: Carbon::parse($validated['end']),
            eventStart: isset($validated['event_start']) ? Carbon::parse($validated['event_start']) : null,
            eventEnd: isset($validated['event_end']) ? Carbon::parse($validated['event_end']) : null,
            url: $validated['url'] ?? null,
        );

        return redirect()->route('admin.events.suggestions')->with('alert-success', 'Das Event wurde akzeptiert!');
    }

    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate(self::VALIDATOR_RULES);

        $station = null;
        if (isset($validated['nearest_station_name'])) {
            $station = $this->dataProvider->getStations($validated['nearest_station_name'], 1)->first();
            if ($station === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
        }

        $this->eventService->createEvent(
            name: $validated['name'],
            hashtag: $validated['hashtag'] ?? null,
            host: $validated['host'] ?? null,
            station: $station,
            checkinStart: Carbon::parse($validated['checkin_start']),
            checkinEnd: Carbon::parse($validated['checkin_end']),
            eventStart: isset($validated['event_start']) ? Carbon::parse($validated['event_start']) : null,
            eventEnd: isset($validated['event_end']) ? Carbon::parse($validated['event_end']) : null,
            url: $validated['url'] ?? null,
            acceptedBy: auth()->user(),
        );

        return redirect()->route('admin.events')->with('alert-success', 'The event was created!');
    }

    public function edit(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate(self::VALIDATOR_RULES);
        $event = Event::findOrFail($id);

        if (($validated['nearest_station_name'] ?? '') === '') {
            $station = null;
        } elseif ($validated['nearest_station_name'] !== $event->station?->name) {
            $station = $this->dataProvider->getStations($validated['nearest_station_name'], 1)->first();
            if ($station === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
        } else {
            $station = $event->station;
        }

        $this->eventService->updateEvent(
            event: $event,
            name: $validated['name'],
            hashtag: $validated['hashtag'] ?? null,
            host: $validated['host'] ?? null,
            station: $station,
            checkinStart: Carbon::parse($validated['checkin_start']),
            checkinEnd: Carbon::parse($validated['checkin_end']),
            eventStart: isset($validated['event_start']) ? Carbon::parse($validated['event_start']) : null,
            eventEnd: isset($validated['event_end']) ? Carbon::parse($validated['event_end']) : null,
            url: $validated['url'] ?? null,
        );

        return redirect()->route('admin.events')->with('alert-success', 'Das Event wurde bearbeitet!');
    }

    public function deleteEvent(Request $request): RedirectResponse
    {
        $validated = $request->validate(['id' => ['required', 'exists:events,id']]);
        Event::findOrFail($validated['id'])->delete();

        return redirect()->route('admin.events')->with('alert-success', 'Das Event wurde gelöscht!');
    }
}
