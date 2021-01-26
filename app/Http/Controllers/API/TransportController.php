<?php

namespace App\Http\Controllers\API;

use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\HafasTrip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransportController extends ResponseController
{
    public function TrainAutocomplete($station)
    {
        $trainAutocompleteResponse = TransportBackend::TrainAutocomplete($station);
        return $this->sendResponse($trainAutocompleteResponse);
    }

    public function TrainStationboard(Request $request) {
        $validator = Validator::make($request->all(), [
            'station'    => ['required', 'string'],
            'when'       => ['nullable', 'date'],
            'travelType' => ['nullable', Rule::in('nationalExpress', 'national', 'regionalExp', 'regional', 'suburban', 'bus', 'ferry', 'subway', 'tram', 'taxi')]
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $validated = $validator->validate();

        $trainStationboardResponse = TransportBackend::TrainStationboard(
            $validated['station'],
            isset($validated['when']) ? Carbon::parse($validated['when']) : null,
            $validated['travelType'] ?? null
        );
        if ($trainStationboardResponse === false) {
            return $this->sendError(400, __('controller.transport.no-name-given'));
        }
        if ($trainStationboardResponse === null) {

            return $this->sendError(404, __('controller.transport.no-station-found'));
        }

        return $this->sendResponse([
                                       'station'    => $trainStationboardResponse['station'],
                                       'when'       => $trainStationboardResponse['when'],
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

    public function TrainCheckin(Request $request) {
        $validator = Validator::make($request->all(), [
            'tripID'      => 'required',
            'lineName'    => ['nullable'], //Should be required in future API Releases due to DB Rest
            'start'       => 'required',
            'destination' => 'required',
            'body'        => 'max:280',
            'tweet'       => 'boolean',
            'toot'        => 'boolean'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        $hafasTrip = HafasTrip::where('trip_id', $request->input('tripID'))->first();

        if ($hafasTrip == null && strlen($request->input('lineName')) == 0) {
            return $this->sendError('Please specify the trip with lineName.', 400);
        } else if ($hafasTrip == null) {
            $hafasTrip = HafasController::getHafasTrip($request->input('tripID'), $request->input('lineName'));
        }

        try {
            $trainCheckinResponse = TransportBackend::TrainCheckin(
                $hafasTrip->trip_id,
                $request->input('start'),
                $request->input('destination'),
                $request->input('body'),
                auth()->user(),
                0,
                $request->input('tweet'),
                $request->input('toot')
            );

            return $this->sendResponse([
                                           'distance'             => $trainCheckinResponse['distance'],
                                           'duration'             => $trainCheckinResponse['duration'],
                                           'statusId'             => $trainCheckinResponse['statusId'],
                                           'points'               => $trainCheckinResponse['points'],
                                           'lineName'             => $trainCheckinResponse['lineName'],
                                           'alsoOnThisConnection' => $trainCheckinResponse['alsoOnThisConnection']
                                               ->map(function($status) {
                                                   return $status->user;
                                               })
                                       ]);

        } catch (CheckInCollisionException $e) {

            return $this->sendError([
                                        'status_id' => $e->getCollision()->status_id,
                                        'lineName'  => $e->getCollision()->HafasTrip->first()->linename
                                    ], 409);

        } catch (\Throwable $e) {
            return $this->sendError('Unknown Error occured', 500);
        }

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

        $nearestStation = HafasController::getNearbyStations($request->latitude, $request->longitude, 1)->first();
        if ($nearestStation === null) {
            return $this->sendError(__("controller.transport.no-station-found"), 404);
        }

        return $this->sendResponse($nearestStation);
    }

    public function getHome()
    {
        $home = auth()->user()->home;
        if($home === null) {
            return $this->sendError('user has not set a home station.');
        }
        return $this->sendResponse($home);
    }

    public function setHome(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ibnr' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        try {
            $trainStation = TransportBackend::setHome(Auth::user(), $request->ibnr);
            return $this->sendResponse($trainStation->name);
        } catch (HafasException $e) {
            return $this->sendError([
                                        'id'      => 'HAFAS_EXCEPTION',
                                        'message' => $e->getMessage()
                                    ]);
        }
    }
}
