<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\Enum\ContributionActionType;
use App\Enum\EventRejectionReason;
use App\Exceptions\DataProviderException;
use App\Http\Controllers\Backend\Admin\EventController as AdminEventBackend;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Notifications\EventSuggestionProcessed;
use App\Services\Contribution\ContributionXPService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class EventController extends Controller
{
    private DataProviderInterface $dataProvider;

    public function __construct()
    {
        $this->dataProvider = (new DataProviderBuilder())->build();
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
        $queryBase = Event::query();
        if ($request->has('query')) {
            $queryBase->where('name', 'LIKE', '%' . strip_tags($request->get('query')) . '%');
        }

        $today = today()->toDateString();

        return view('admin.events.index', [
            'events_future' => $queryBase->clone()
                ->orderBy('checkin_start')
                ->where('checkin_start', '>', $today)
                ->paginate(10, pageName: 'future'),
            'events_current' => $queryBase->clone()
                ->orderBy('checkin_start')
                ->where('checkin_start', '<=', $today)
                ->where('checkin_end', '>=', $today)
                ->paginate(10, pageName: 'current'),
            'events_past' => $queryBase->clone()
                ->where('checkin_end', '<', $today)
                ->paginate(10, pageName: 'past'),
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
        $parallelEvents = Event::where([
            ['checkin_start', '>=', $suggestion->begin->toDateString()],
            ['checkin_end', '<=', $suggestion->end->toDateString()],
        ])
            ->orWhere(function ($query) use ($suggestion): void {
                $query->where([
                    ['checkin_start', '<=', $suggestion->begin->toDateString()],
                    ['checkin_end', '>=', $suggestion->begin->toDateString()],
                ]);
            })
            ->orWhere(function ($query) use ($suggestion): void {
                $query->where([
                    ['checkin_start', '<=', $suggestion->end->toDateString()],
                    ['checkin_end', '>=', $suggestion->end->toDateString()],
                ]);
            })
            ->get();

        $parallelEvents->map(function ($event) use ($suggestion) {
            similar_text($event->name, $suggestion->name, $perc);
            $event->similarity = $perc;

            return $event;
        });

        return view('admin.events.suggestion-create', [
            'eventSuggestion' => $suggestion,
            'parallelEvents' => $parallelEvents->sortByDesc('similarity'),
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
            'rejectionReason' => [
                'required', new Enum(EventRejectionReason::class),
            ],
        ]);
        $eventSuggestion = EventSuggestion::find($validated['id']);
        $eventSuggestion->update(['processed' => true]);

        if ($eventSuggestion->admin_notification_id !== null) {
            TelegramService::admin()->deleteMessage($eventSuggestion->admin_notification_id);
        }

        $rejectionReason = EventRejectionReason::from($validated['rejectionReason']);

        $eventSuggestion->user->notify(
            new EventSuggestionProcessed(
                $eventSuggestion,
                null,
                $rejectionReason,
            )
        );

        if ($eventSuggestion->user !== null && $rejectionReason->getXPChange() !== 0) {
            ContributionXPService::grantXP(
                user: $eventSuggestion->user,
                xpChange: $rejectionReason->getXPChange(),
                action: ContributionActionType::EVENT_SUGGESTED,
                entityType: 'event_suggestion',
                entityId: $eventSuggestion->id,
                note: 'Event denied: ' . $rejectionReason->value,
            );
        }

        return redirect()->route('admin.events.suggestions')->with('alert-success', 'Event denied.');
    }

    /**
     * @throws DataProviderException
     */
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

        $eventSuggestion = EventSuggestion::find($validated['suggestionId']);
        $station = null;

        if ($eventSuggestion->user_id === auth()->user()->id && !auth()->user()?->hasRole('admin')) {
            return back()->with('alert-danger', 'You can\'t accept your own suggestion.');
        }

        if (isset($validated['nearest_station_name'])) {
            $station = $this->dataProvider->getStations($validated['nearest_station_name'], 1)->first();

            if ($station === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
        }

        $event = Event::create([
            'name' => $validated['name'],
            'slug' => AdminEventBackend::createSlugFromName($validated['name']),
            'hashtag' => $validated['hashtag'],
            'host' => $validated['host'],
            'station_id' => $station?->id,
            'checkin_start' => Carbon::parse($validated['begin'])->toDateString(),
            'checkin_end' => Carbon::parse($validated['end'])->toDateString(),
            'event_start' => isset($validated['event_start']) ? Carbon::parse($validated['event_start'])->toDateString() : null,
            'event_end' => isset($validated['event_end']) ? Carbon::parse($validated['event_end'])->toDateString() : null,
            'url' => $validated['url'] ?? null,
            'accepted_by' => auth()->user()->id,
        ]);

        $eventSuggestion->update(['processed' => true]);

        if ($eventSuggestion->admin_notification_id !== null) {
            TelegramService::admin()->deleteMessage($eventSuggestion->admin_notification_id);
        }

        $eventSuggestion->user->notify(new EventSuggestionProcessed($eventSuggestion, $event));

        if ($eventSuggestion->user !== null) {
            ContributionXPService::grantXP(
                user: $eventSuggestion->user,
                xpChange: ContributionXPService::getXPForEventApproval(),
                action: ContributionActionType::EVENT_SUGGESTED,
                entityType: 'event_suggestion',
                entityId: $eventSuggestion->id,
                note: 'Event approved: ' . $event->name,
            );
        }

        return redirect()->route('admin.events.suggestions')->with('alert-success', 'Das Event wurde akzeptiert!');
    }

    /**
     * @throws DataProviderException
     */
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

        $validated['slug'] = AdminEventBackend::createSlugFromName($validated['name']);
        $validated['station_id'] = $station?->id;
        $validated['checkin_start'] = Carbon::parse($validated['checkin_start'])->toDateString();
        $validated['checkin_end'] = Carbon::parse($validated['checkin_end'])->toDateString();
        if (isset($validated['event_start'])) {
            $validated['event_start'] = Carbon::parse($validated['event_start'])->toDateString();
        }
        if (isset($validated['event_end'])) {
            $validated['event_end'] = Carbon::parse($validated['event_end'])->toDateString();
        }
        $validated['accepted_by'] = auth()->user()->id;

        Event::create($validated);

        return redirect()->route('admin.events')->with('alert-success', 'The event was created!');
    }

    public function edit(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate(self::VALIDATOR_RULES);

        $event = Event::findOrFail($id);

        if (strlen($validated['nearest_station_name'] ?? '') === 0) {
            $validated['station_id'] = null;
        } elseif ($validated['nearest_station_name'] && $validated['nearest_station_name'] !== $event->station->name) {
            $station = $this->dataProvider->getStations($validated['nearest_station_name'], 1)->first();

            if ($station === null) {
                return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
            }
            $validated['station_id'] = $station->id;
        }

        $event->update($validated);

        return redirect()->route('admin.events')->with('alert-success', 'Das Event wurde bearbeitet!');
    }

    public function deleteEvent(Request $request): RedirectResponse
    {
        $validated = $request->validate(['id' => ['required', 'exists:events,id']]);
        $event = Event::find($validated['id']);
        $event->delete();

        return redirect()->route('admin.events')->with('alert-success', 'Das Event wurde gelöscht!');
    }
}
