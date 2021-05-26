<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Controllers\Backend\Admin\EventController as AdminEventBackend;
use App\Http\Controllers\Backend\Admin\TelegramController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HafasController;
use App\Models\Event;
use App\Models\EventSuggestion;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function renderList(): Renderable {
        return view('admin.events.list', [
            'events' => Event::where('end', '>=', Carbon::now()->toIso8601String())->get()
        ]);
    }

    public function renderSuggestions(): Renderable {
        return view('admin.events.suggestions', [
            'suggestions' => EventSuggestion::where('processed', false)->get()
        ]);
    }

    public function renderSuggestionCreation(int $id): Renderable {
        return view('admin.events.suggestion-create', [
            'event' => EventSuggestion::findOrFail($id)
        ]);
    }

    public function renderCreate(): Renderable {
        return view('admin.events.create');
    }

    public function renderEdit(int $id): Renderable {
        return view('admin.events.edit', ['event' => Event::findOrFail($id)]);
    }

    public function denySuggestion(Request $request): RedirectResponse {
        $validated       = $request->validate(['id' => ['required', 'exists:event_suggestions,id']]);
        $eventSuggestion = EventSuggestion::find($validated['id']);
        $eventSuggestion->update(['processed' => true]);
        TelegramController::sendAdminMessage(
            auth()->user()->name . ' hat den Veranstaltungsvorschlag "' . $eventSuggestion->name . '" abgelehnt.'
        );
        return back()->with('alert-success', 'Vorschlag abgelehnt.');
    }

    public function acceptSuggestion(Request $request): RedirectResponse {
        $validated       = $request->validate([
                                                  'suggestionId'         => ['required', 'exists:event_suggestions,id'],
                                                  'name'                 => ['required', 'max:255'],
                                                  'hashtag'              => ['required', 'max:30'],
                                                  'host'                 => ['required', 'max:255'],
                                                  'url'                  => ['nullable', 'url'],
                                                  'nearest_station_name' => ['required', 'max:255'],
                                                  'begin'                => ['required', 'date'],
                                                  'end'                  => ['required', 'date'],
                                              ]);
        $eventSuggestion = EventSuggestion::find($validated['suggestionId']);

        $trainStation = HafasController::getStations($validated['nearest_station_name'], 1)->first();

        if ($trainStation == null) {
            return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
        }

        AdminEventBackend::createEvent(
            name: $validated['name'],
            hashtag: $validated['hashtag'],
            host: $validated['host'],
            trainStation: $trainStation,
            begin: Carbon::parse($validated['begin']),
            end: Carbon::parse($validated['end']),
            url: $validated['url']
        );

        $eventSuggestion->update(['processed' => true]);
        TelegramController::sendAdminMessage(
            auth()->user()->name . ' hat den Veranstaltungsvorschlag "' . $eventSuggestion->name . '" akzeptiert.'
        );

        return redirect()->route('admin.events')->with('alert-success', 'Das Event wurde akzeptiert!');
    }

    public function create(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'name'                 => ['required', 'max:255'],
                                            'hashtag'              => ['required', 'max:30'],
                                            'host'                 => ['required', 'max:255'],
                                            'url'                  => ['nullable', 'url'],
                                            'nearest_station_name' => ['required', 'max:255'],
                                            'begin'                => ['required', 'date'],
                                            'end'                  => ['required', 'date'],
                                        ]);

        $trainStation = HafasController::getStations($validated['nearest_station_name'], 1)->first();

        if ($trainStation == null) {
            return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
        }

        AdminEventBackend::createEvent(
            name: $validated['name'],
            hashtag: $validated['hashtag'],
            host: $validated['host'],
            trainStation: $trainStation,
            begin: Carbon::parse($validated['begin']),
            end: Carbon::parse($validated['end']),
            url: $validated['url']
        );

        return redirect()->route('admin.events')->with('alert-success', 'Das Event wurde erstellt!');
    }

    public function edit(int $id, Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'name'                 => ['required', 'max:255'],
                                            'hashtag'              => ['required', 'max:30'],
                                            'host'                 => ['required', 'max:255'],
                                            'url'                  => ['nullable', 'url'],
                                            'nearest_station_name' => ['required', 'max:255'],
                                            'begin'                => ['required', 'date'],
                                            'end'                  => ['required', 'date'],
                                        ]);

        $event = Event::findOrFail($id);

        $trainStation = HafasController::getStations($validated['nearest_station_name'], 1)->first();

        if ($trainStation == null) {
            return back()->with('alert-danger', 'Die Station konnte nicht gefunden werden.');
        }
        $validated['trainstation'] = $trainStation->id;
        unset($validated['nearest_station_name']);

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
