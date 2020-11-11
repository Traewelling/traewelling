<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Http\Controllers\TransportController as transportBackend;
use App\Http\Controllers\EventController as EventBackend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendTransportController extends Controller
{
    public function trainAutocomplete($station) {
        $trainAutocompleteResponse = transportBackend::trainAutocomplete($station);
        return response()->json($trainAutocompleteResponse);
    }

    public function trainStationboard(Request $request) {
        $trainStationboardResponse = transportBackend::trainStationboard(
            $request->station,
            $request->when,
            $request->travelType
        );
        if ($trainStationboardResponse === false) {
            return redirect()->back()->with('error', __('controller.transport.no-name-given'));
        }
        if ($trainStationboardResponse === null) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
        }

        return view('stationboard', [
                                      'station'    => $trainStationboardResponse['station'],
                                      'departures' => $trainStationboardResponse['departures'],
                                      'when'       => $trainStationboardResponse['when'],
                                      'request'    => $request,
                                      'latest'     => TransportController::getLatestArrivals(Auth::user())
                                  ]
        );
    }

    public function stationByCoordinates(Request $request) {
        $validatedInput = $request->validate([
                                                 'latitude'  => 'required|numeric|min:-180|max:180',
                                                 'longitude' => 'required|numeric|min:-180|max:180'
                                             ]);

        $nearestStation = transportBackend::stationByCoordinates($validatedInput['latitude'],
                                                                 $validatedInput['longitude']);
        if ($nearestStation === null) {
            return redirect()->back()->with('error', __('controller.transport.no-station-found'));
        }

        return redirect(route('trains.stationboard', [
            'station'  => $nearestStation->name,
            'provider' => 'train'
        ]));
    }

    public function trainTrip(Request $request) {
        $trainTripResponse = transportBackend::trainTrip(
            $request->tripID,
            $request->lineName,
            $request->start
        );
        if ($trainTripResponse === null) {
            return redirect()->back()->with('error', __('controller.transport.not-in-stopovers'));
        }

        // Find out where this train terminates and offer this as a "fast check-in" option.
        $terminalStopIndex = count($trainTripResponse['stopovers']) - 1;
        while ($terminalStopIndex >= 1
            && isset($trainTripResponse['stopovers'][$terminalStopIndex]['cancelled'])
            && $trainTripResponse['stopovers'][$terminalStopIndex]['cancelled']) {
            $terminalStopIndex--;
        }
        $terminalStop = $trainTripResponse['stopovers'][$terminalStopIndex];

        return view('trip', [
            'hafasTrip'    => $trainTripResponse['hafasTrip'],
            'destination'  => $trainTripResponse['destination'], //deprecated. use hafasTrip->destinationStation instead
            'events'       => EventBackend::activeEvents(),
            'start'        => $trainTripResponse['start'], //deprecated. use hafasTrip->originStation instead
            'stopovers'    => $trainTripResponse['stopovers'],
            'terminalStop' => $terminalStop,
            'user'         => Auth::user(),
        ]);
    }

    public function trainCheckin(Request $request) {
        $this->validate($request, [
            'body'           => 'max:280',
            'business_check' => 'max:0', // Wenn wir Businesstrips wieder einbringen, kann man das wieder auf mehr stellen.
            'tweet_check'    => 'max:2',
            'toot_check'     => 'max:2',
            'event'          => 'integer'
        ]);
        try {
            $trainCheckin = transportBackend::trainCheckin(
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
            $trainStation = transportBackend::setHome(Auth::user(), $validated['ibnr']);

            return redirect()->back()->with(['message' => __('user.home-set', ['station' => $trainStation->name])]);
        } catch (HafasException $e) {
            return redirect()->back()->with(['error' => __('messages.exception.generalHafas')]);
        }
    }

    public function fastTripAccess(Request $request) {
        $fastTripResponse = transportBackend::fastTripAccess($request->start,
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
