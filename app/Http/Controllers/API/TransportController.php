<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\TransportController as TransportBackend;
use App\TrainStations;
use Illuminate\Http\Request;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Facades\Auth;
use Validator;


class TransportController extends ResponseController {
    public function TrainAutocomplete(Request $request, $station) {
        $TrainAutocompleteResponse = TransportBackend::TrainAutocomplete($station);
        return $this->sendResponse($TrainAutocompleteResponse);
    }

    public function TrainStationboard(Request $request) {
        $validator = Validator::make($request->all(), [
            'station' => 'string|required',
            'travelType' => 'string|in:nationalExpress,national,regionalExp,regional,suburban,bus,ferry,subway,tram,taxi'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $TrainStationboardResponse = TransportBackend::TrainStationboard(
            $request->station,
            $request->when,
            $request->travelType
        );
        if ($TrainStationboardResponse === false) {
            return $this->sendError(400, __('controller.transport.no-name-given'));
        }
        if ($TrainStationboardResponse === null) {

            return  $this->sendError(404, __('controller.transport.no-station-found'));
        }

        return $this->sendResponse([
                                      'station' => $TrainStationboardResponse['station'],
                                      'when' => $TrainStationboardResponse['when'],
                                      'departures' => $TrainStationboardResponse['departures']
                                  ]);
    }

    public function TrainTrip(Request $request) {
        $validator = Validator::make($request->all(), [
            'tripID' => 'required',
            'lineName' => 'required',
            'start' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $TrainTripResponse = TransportBackend::TrainTrip(
            $request->tripID,
            $request->lineName,
            $request->start
        );
        if ($TrainTripResponse === null) {
            return $this->sendError(__('controller.transport.not-in-stopovers'), 400);
        }

        return $this->sendResponse([
            'start' => $TrainTripResponse['start'],
            'destination' => $TrainTripResponse['destination'],
            'train' => $TrainTripResponse['train'],
            'stopovers' => $TrainTripResponse['stopovers']
        ]);
    }

    public function TrainCheckin(Request $request) {
        $validator = Validator::make($request->all(), [
            'tripID' => 'required',
            'start' => 'required',
            'destination' => 'required',
            'body' => 'max:280',
            'tweet' => 'boolean',
            'toot' => 'boolean'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $TrainCheckinResponse = TransportBackend::TrainCheckin(
            $request->tripID,
            $request->start,
            $request->destination,
            $request->body,
            Auth::user(),
            0,
            $request->tweet,
            $request->toot
        );

        if ($TrainCheckinResponse['success'] === false) {
            return $this->sendError([
                        'status_id' => $TrainCheckinResponse['overlap']->id,
                        'lineName' => $TrainCheckinResponse['overlap']->HafasTrip()->first()->linename
                    ], 409);
        }

        if ($TrainCheckinResponse['success'] === true) {
            return $this->sendResponse([
                'distance' => $TrainCheckinResponse['distance'],
                'duration' => $TrainCheckinResponse['duration'],
                'points' => $TrainCheckinResponse['points'],
                'lineName' => $TrainCheckinResponse['lineName'],
                'alsoOnThisConnection' => $TrainCheckinResponse['alsoOnThisConnection']
            ]);
        }
    }

    public function TrainLatestArrivals(Request $request){
        $arrivals = TransportBackend::getLatestArrivals(Auth::user());

        return $this->sendResponse($arrivals);
    }

    public function getHome(Request $request) {
        return $this->sendResponse(Auth::user()->home);
    }

    public function setHome(Request $request) {
        $validator = Validator::make($request->all(), [
            'ibnr' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        $SetHomeResponse = TransportBackend::SetHome(Auth::user(), $request->ibnr);
        return $this->sendResponse($SetHomeResponse);
    }
}
