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
            'business_check' => 'max:2',
            'tweet_check' => 'max:2',
            'toot_check' => 'max:2'
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
                ->withErrors(__(
                    'controller.transport.overlapping-checkin',
                    ['url' => url('/status/'.$TrainCheckinResponse['overlap']->id), 'id' => $TrainCheckinResponse['overlap']->id]
                ));

        }

        if ($TrainCheckinResponse['success'] === true) {
            $alsoOnThisTrain = [];

            foreach ($TrainCheckinResponse['alsoOnThisTrain'] as $traveler) {
                $user = $traveler->status->user;
                $alsoOnThisTrain[] =
                    "<a href=\"" .
                    route('account.show',  ['username' => $user->username]) .
                    "\">" .
                    $user->name .
                    " (@" . $user->username . ")</a>";
            }
            $concatSameTrain = implode(', ', $alsoOnThisTrain);
            if (!empty($concatSameTrain)) {
                $concatSameTrain =
                    "<br />" .
                    trans_choice(
                        'controller.transport.also-in-connection',
                        count($TrainCheckinResponse['alsoOnThisTrain']),
                        ['people' => $concatSameTrain]
                    );
            }
            return redirect()->route('dashboard')->with(
                'success',
                __('controller.transport.checkin-ok', ['pts' => $TrainCheckinResponse['points']])
                . $concatSameTrain
            )->with('message', __('controller.transport.checkin-meta', [
                'distance' => $TrainCheckinResponse['distance'],
                'duration' => gmdate('H:i', $TrainCheckinResponse['duration'])
            ]));
        }


    }

    public function SetHome(Request $request) {
        $SetHomeResponse = TransportBackend::SetHome(Auth::user(), $request->ibnr);
        if ($SetHomeResponse === true) {
            return redirect()->back();
        }
        return redirect()->back()->with(['message' => __('user.home-set', ['station' => $SetHomeResponse])]);
    }

}
