<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Status;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EventController extends Controller
{

    public static function all(): Collection {
        return Event::orderBy('end', 'desc')->get();
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return RedirectResponse
     * @throws \App\Exceptions\HafasException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @deprecated
     */
    public static function save(Request $request, Event $event): RedirectResponse {
        $validated         = $request->validate([
                                                    'name'                 => 'required|max:255',
                                                    'hashtag'              => 'required|max:30',
                                                    'host'                 => 'required|max:255',
                                                    'url'                  => 'url',
                                                    'nearest_station_name' => 'required',
                                                    'begin'                => 'required|date',
                                                    'end'                  => 'required|date'
                                                ]);
        $validated['slug'] = Str::slug($validated['name'], '_');

        // Unique slugs
        if ($event->id == 0) {
            $i = "";
            while (Event::where('slug', '=', $validated['slug'] . $i)->first()) {
                $i = $i == "" ? 1 : $i + 1;
            }
            if ($i != "") {
                $validated['slug'] = $validated['slug'] . $i;
            }
        }

        // Make the events fill the whole day, so they're including the last day.
        $validated['begin']         = new Carbon($validated['begin']);
        $validated['begin']->hour   = 0;
        $validated['begin']->minute = 0;
        $validated['begin']->second = 0;
        $validated['end']           = new Carbon($validated['end']);
        $validated['end']->hour     = 23;
        $validated['end']->minute   = 59;
        $validated['end']->second   = 59;

        $event->fill($validated);

        $client      = new Client(['base_uri' => config('trwl.db_rest')]);
        $response    = $client->request('GET', "locations?query=" . $validated['nearest_station_name'])
                              ->getBody()
                              ->getContents();
        $ibnrObjekte = json_decode($response, true);

        $event->trainstation = TransportController::getTrainStation(
            $ibnrObjekte[0]['id'],
            $ibnrObjekte[0]['name'],
            $ibnrObjekte[0]['location']['latitude'],
            $ibnrObjekte[0]['location']['longitude']
        )->id;

        $event->save();

        return redirect()->route('events.show', ['slug' => $event->slug])
                         ->with('message', $event->slug . " saved.");
    }


    public static function getBySlug(string $slug): ?Event {
        return Event::where('slug', '=', $slug)->firstOrFail();
    }

    /**
     * @param string $slug
     * @return string
     * @deprecated
     */
    public static function destroy(string $slug): string {
        $event = Event::where('slug', '=', $slug)->firstOrFail();
        Status::where('event_id', '=', $event->id)->update(['event_id' => null]);
        $event->delete();

        return $slug;
    }

    public static function activeEvents(): ?Collection {
        $now = Carbon::now();

        return Event::where([
                                ['begin', '<=', $now],
                                ['end', '>=', $now]
                            ])->get();
    }
}
