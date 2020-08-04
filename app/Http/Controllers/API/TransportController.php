<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\TransportController as TransportBackend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransportController extends ResponseController
{
    public function TrainAutocomplete($station)
    {
        $trainAutocompleteResponse = TransportBackend::TrainAutocomplete($station);
        return $this->sendResponse($trainAutocompleteResponse);
    }

    public function TrainStationboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'station' => 'string|required',
            'travelType' => 'string|in:nationalExpress,
                                        national,
                                        regionalExp,
                                        regional,
                                        suburban,
                                        bus,
                                        ferry,
                                        subway,
                                        tram,
                                        taxi'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $trainStationboardResponse = TransportBackend::TrainStationboard(
            $request->station,
            $request->when,
            $request->travelType
        );
        if ($trainStationboardResponse === false) {
            return $this->sendError(400, __('controller.transport.no-name-given'));
        }
        if ($trainStationboardResponse === null) {

            return  $this->sendError(404, __('controller.transport.no-station-found'));
        }

        return $this->sendResponse([
                                      'station' => $trainStationboardResponse['station'],
                                      'when' => $trainStationboardResponse['when'],
                                      'departures' => $trainStationboardResponse['departures']
                                  ]);
    }

    public function TrainTrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tripID' => 'required',
            'lineName' => 'required',
            'start' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $trainTripResponse = TransportBackend::TrainTrip(
            $request->tripID,
            $request->lineName,
            $request->start
        );
        if ($trainTripResponse === null) {
            return $this->sendError(__('controller.transport.not-in-stopovers'), 400);
        }

        return $this->sendResponse([
            'start' => $trainTripResponse['start'],
            'destination' => $trainTripResponse['destination'],
            'train' => $trainTripResponse['train'],
            'stopovers' => $trainTripResponse['stopovers']
        ]);
    }

    public function TrainCheckin(Request $request)
    {
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

        $trainCheckinResponse = TransportBackend::TrainCheckin(
            $request->input('tripID'),
            $request->input('start'),
            $request->input('destination'),
            $request->input('body'),
            auth()->user(),
            0,
            $request->input('tweet'),
            $request->input('toot')
        );

        if ($trainCheckinResponse['success'] === false) {
            return $this->sendError([
                        'status_id' => $trainCheckinResponse['overlap']->id,
                        'lineName' => $trainCheckinResponse['overlap']->HafasTrip()->first()->linename
                    ], 409);
        }

        if ($trainCheckinResponse['success'] === true) {
            return $this->sendResponse([
                'distance' => $trainCheckinResponse['distance'],
                'duration' => $trainCheckinResponse['duration'],
                'points' => $trainCheckinResponse['points'],
                'lineName' => $trainCheckinResponse['lineName'],
                'alsoOnThisConnection' => $trainCheckinResponse['alsoOnThisConnection']
            ]);
        }
        return $this->sendError('Unknown Error occured', 500);
    }

    public function TrainLatestArrivals()
    {
        $arrivals = TransportBackend::getLatestArrivals(auth()->user());

        return $this->sendResponse($arrivals);
    }

    public function StationByCoordinates(Request $request) {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|min:-180|max:180',
            'longitude' => 'required|numeric|min:-180|max:180'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        
        $nearestStation = TransportBackend::StationByCoordinates($request->latitude, $request->longitude);
        if ($nearestStation === null) {
            return $this->sendError(__("controller.transport.no-station-found"), 404);
        }

        return $this->sendResponse($nearestStation);
    }

    public function getHome()
    {
        return $this->sendResponse(auth()->user()->home);
    }

    public function setHome(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ibnr' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        $setHomeResponse = TransportBackend::SetHome(Auth::user(), $request->ibnr);
        return $this->sendResponse($setHomeResponse);
    }
}
