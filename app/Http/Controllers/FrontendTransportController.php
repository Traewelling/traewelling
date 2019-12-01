<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\TransportController as TransportBackend;
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
        ]
        );
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

        return view('trip', [
            'train' => $TrainTripResponse['train'],
            'stopovers' => $TrainTripResponse['stopovers'],
            'destination' => $TrainTripResponse['destination'],
            'start' => $TrainTripResponse['start']
        ]);
    }

    public function TrainCheckin(Request $request) {
        $this->validate($request, [
            'body' => 'max:280',
            'business_check' => 'max:0', // Wenn wir Businesstrips wieder einbringen, kann man das wieder auf mehr stellen.
            'tweet_check' => 'max:2',
            'toot_check' => 'max:2',
        ]);
        $TrainCheckinResponse = TransportBackend::TrainCheckin(
            $request->tripID,
            $request->start,
            $request->destination,
            $request->body,
            Auth::user(),
            $request->business_check,
            $request->tweet_check,
            $request->toot_check
            );

        if ($TrainCheckinResponse['success'] === false) {
            return redirect()
                ->route('dashboard')
                ->with('error', __(
                        'controller.transport.overlapping-checkin',
                        [
                            'url' => url('/status/'.$TrainCheckinResponse['overlap']->id),
                            'id' => $TrainCheckinResponse['overlap']->id,
                            'linename' => $TrainCheckinResponse['overlap']->HafasTrip()->first()->linename
                        ]
                    ));
        }

        if ($TrainCheckinResponse['success'] === true) {
            return redirect()->route('dashboard')->with('checkin-success', [
                'distance' => $TrainCheckinResponse['distance'],
                'duration' => $TrainCheckinResponse['duration'],
                'points' => $TrainCheckinResponse['points'],
                'lineName' => $TrainCheckinResponse['lineName'],
                'alsoOnThisConnection' => $TrainCheckinResponse['alsoOnThisConnection']
            ]);
        }
    }

    public function SetHome(Request $request) {
        $SetHomeResponse = TransportBackend::SetHome(Auth::user(), $request->ibnr);
        if ($SetHomeResponse === true) {
            return redirect()->back();
        }
        return redirect()->back()->with(['message' => __('user.home-set', ['station' => $SetHomeResponse])]);
    }

    public function FastTripAccess(Request $request) {
        $FastTripResponse = TransportBackend::FastTripAccess($request->start, $request->lineName, $request->number, $request->when);
        if ($FastTripResponse === null) {
            abort(404);
        }
        return redirect()->route('trains.trip', ['tripID'=>$FastTripResponse->tripId,'lineName'=>$FastTripResponse->line->name,'start'=>$request->start]);
    }

}
