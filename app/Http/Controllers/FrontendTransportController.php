<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Http\Controllers\EventController as EventBackend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendTransportController extends Controller
{
    public function TrainAutocomplete($station) {
        $TrainAutocompleteResponse = TransportBackend::TrainAutocomplete($station);
        return response()->json($TrainAutocompleteResponse);
    }

    public function BusAutocomplete($station) {
        $BusAutocompleteResponse = TransportBackend::BusAutocomplete($station);
        return response()->json($BusAutocompleteResponse);
    }

    public function TrainStationboard(Request $request) {
        $TrainStationboardResponse = TransportBackend::TrainStationboard(
            $request->station,
            $request->when,
            $request->travelType
        );
        if ($TrainStationboardResponse === false) {
            return redirect()->back()->with('error', __('controller.transport.no-name-given'));
        }
        if ($TrainStationboardResponse === null) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
        }

        return view('stationboard', [
            'station' => $TrainStationboardResponse['station'],
            'departures' => $TrainStationboardResponse['departures'],
            'when' => $TrainStationboardResponse['when'],
            'request' => $request,
            'latest' => TransportController::getLatestArrivals(Auth::user())
        ]
        );
    }

    public function StationByCoordinates(Request $request)
    {
        $validatedInput = $request->validate([
            'latitude' => 'required|numeric|min:-180|max:180',
            'longitude' => 'required|numeric|min:-180|max:180'
        ]);

        $nearestStation = TransportBackend::StationByCoordinates($validatedInput['latitude'], $validatedInput['longitude']);
        if ($nearestStation === null) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
        }

        return redirect(route('trains.stationboard', [
            'station' => $nearestStation->name,
            'provider' => 'train'
        ]));
    }

    public function TrainTrip(Request $request) {
        $TrainTripResponse = TransportBackend::TrainTrip(
            $request->tripID,
            $request->lineName,
            $request->start
        );
        if ($TrainTripResponse === null) {
            return redirect()->back()->with('error', __('controller.transport.not-in-stopovers'));
        }

        // Find out where this train terminates and offer this as a "fast check-in" option.
        $terminalStopIndex = count($TrainTripResponse['stopovers']) - 1;
        while($terminalStopIndex >= 1 && @$TrainTripResponse['stopovers'][$terminalStopIndex]['cancelled'] == true) {
            $terminalStopIndex--;
        }
        $terminalStop = $TrainTripResponse['stopovers'][$terminalStopIndex];

        return view('trip', [
            'hafasTrip'    => $TrainTripResponse['hafasTrip'],
            'destination'  => $TrainTripResponse['destination'], //deprecated. use hafasTrip->destinationStation instead
            'events'       => EventBackend::activeEvents(),
            'start'        => $TrainTripResponse['start'], //deprecated. use hafasTrip->originStation instead
            'stopovers'    => $TrainTripResponse['stopovers'],
            'terminalStop' => $terminalStop,
            'user'         => Auth::user(),
        ]);
    }

    public function TrainCheckin(Request $request) {
        $this->validate($request, [
            'body' => 'max:280',
            'business_check' => 'max:0', // Wenn wir Businesstrips wieder einbringen, kann man das wieder auf mehr stellen.
            'tweet_check' => 'max:2',
            'toot_check' => 'max:2',
            'event' => 'integer'
        ]);
        try {
            $trainCheckin = TransportBackend::TrainCheckin(
                $request->tripID,
                $request->start,
                $request->destination,
                $request->body,
                Auth::user(),
                $request->business_check,
                $request->tweet_check,
                $request->toot_check,
                $request->event
            );

            return redirect()->route('dashboard')->with('checkin-success', [
                'distance'             => $trainCheckin['distance'],
                'duration'             => $trainCheckin['duration'],
                'points'               => $trainCheckin['points'],
                'lineName'             => $trainCheckin['lineName'],
                'alsoOnThisConnection' => $trainCheckin['alsoOnThisConnection'],
                'event'                => $trainCheckin['event']
            ]);

        } catch (CheckInCollisionException $e) {

            return redirect()
                ->route('dashboard')
                ->with('error', __(
                    'controller.transport.overlapping-checkin',
                    [
                        'url'      => url('/status/' . $e->getCollision()->status->id),
                        'id'       => $e->getCollision()->status->id,
                        'linename' => $e->getCollision()->HafasTrip->linename
                    ]
                ));

        } catch (\Throwable $e) {

            return redirect()
                ->route('dashboard')
                ->with('error', __('messages.exception.general'));

        }

    }

    public function setHome(Request $request) {
        $validated = $request->validate([
                                            'ibnr' => ['required', 'numeric']
                                        ]);

        try {
            $trainStation = TransportBackend::setHome(Auth::user(), $validated['ibnr']);

            return redirect()->back()->with(['message' => __('user.home-set', ['station' => $trainStation->name])]);
        } catch (HafasException $e) {
            return redirect()->back()->with(['error' => __('messages.exception.generalHafas')]);
        }
    }

    public function FastTripAccess(Request $request) {
        $fastTripResponse = TransportBackend::FastTripAccess($request->start,
                                                             $request->lineName,
                                                             $request->number,
                                                             $request->when);
        if ($fastTripResponse === null) {
            abort(404);
        }
        return redirect()->route('trains.trip', [
            'tripID'   => $fastTripResponse->tripId,
            'lineName' => $fastTripResponse->line->name,
            'start'    => $request->start
        ]);
    }

}
