<?php

namespace App\Http\Controllers\API;

use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\HafasTrip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * @deprecated Will be replaced by APIv1
 */
class TransportController extends ResponseController
{
    public function TrainAutocomplete($station): JsonResponse {
        try {
            $trainAutocompleteResponse = TransportBackend::getTrainStationAutocomplete($station)
                                                         ->map(function($station) {
                                                             return [
                                                                 'id'       => $station['ibnr'],
                                                                 'name'     => $station['name'],
                                                                 'provider' => 'train'
                                                             ];
                                                         });
            return $this->sendResponse($trainAutocompleteResponse);
        } catch (HafasException $e) {
            return $this->sendError($e->getMessage(), 503);
        }
    }

    public function TrainStationboard(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'station'    => ['required', 'string'],
            'when'       => ['nullable', 'date'],
            'travelType' => ['nullable', Rule::in(TravelType::getList())]
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $validated = $validator->validate();

        try {
            $trainStationboardResponse = TransportBackend::getDepartures(
                $validated['station'],
                isset($validated['when']) ? Carbon::parse($validated['when']) : null,
                $validated['travelType'] ?? null
            );
        } catch (HafasException $exception) {
            return $this->sendError($exception->getMessage(), 503);
        } catch (ModelNotFoundException) {
            return $this->sendError(__('controller.transport.no-station-found'), 404);
        }

        return $this->sendResponse([
                                       'station'    => $trainStationboardResponse['station'],
                                       'when'       => $trainStationboardResponse['times']['now'],
                                       'departures' => $trainStationboardResponse['departures']
                                   ]);
    }

    public function TrainTrip(Request $request) {
        $validator = Validator::make($request->all(), [
            'tripID'   => 'required',
            'lineName' => 'required',
            'start'    => 'required'
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
                                       'start'       => $trainTripResponse['start'],
                                       'destination' => $trainTripResponse['destination'],
                                       'train'       => $trainTripResponse['train'],
                                       'stopovers'   => $trainTripResponse['stopovers']
                                   ]);
    }

    public function TrainCheckin(Request $request) {
        $validator = Validator::make($request->all(), [
            'tripID'      => 'required',
            'lineName'    => ['nullable'], //Should be required in future API Releases due to DB Rest
            'start'       => ['required', 'numeric'],
            'destination' => ['required', 'numeric'],
            'body'        => 'max:280',
            'tweet'       => 'boolean',
            'toot'        => 'boolean',
            //nullable, so that it is not a breaking change
            'departure'   => ['nullable', 'date'],
            'arrival'     => ['nullable', 'date'],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        $hafasTrip = HafasTrip::where('trip_id', $request->input('tripID'))->first();

        if ($hafasTrip == null && strlen($request->input('lineName')) == 0) {
            return $this->sendError('Please specify the trip with lineName.', 400);
        } elseif ($hafasTrip == null) {
            try {
                $hafasTrip = HafasController::getHafasTrip($request->input('tripID'), $request->input('lineName'));
            } catch (HafasException $exception) {
                return $this->sendError($exception->getMessage(), 400);
            }
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
                $request->input('toot'),
                StatusVisibility::PUBLIC,
                0,
                isset($request->departure) ? Carbon::parse($request->input('departure')) : null,
                isset($request->arrival) ? Carbon::parse($request->input('arrival')) : null,
            );

            return $this->sendResponse([
                                           'distance'             => $trainCheckinResponse['distance'] / 1000,
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

        } catch (StationNotOnTripException) {
            return $this->sendError('Given stations are not on the trip.', 400);
        } catch (Throwable $exception) {
            report($exception);
            return $this->sendError('Unknown Error occurred', 500);
        }

    }

    public function TrainLatestArrivals() {
        $arrivals = TransportBackend::getLatestArrivals(auth()->user());

        return $this->sendResponse($arrivals);
    }

    public function StationByCoordinates(Request $request) {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|min:-180|max:180',
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

    public function getHome() {
        $home = auth()->user()->home;
        if ($home === null) {
            return $this->sendError('user has not set a home station.');
        }
        return $this->sendResponse($home);
    }

    public function setHome(Request $request) {
        $validator = Validator::make($request->all(), [
            'ibnr' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        try {
            $station      = HafasController::getTrainStation($request->ibnr); //Workaround to support APIv1
            $trainStation = TransportBackend::setTrainHome(Auth::user(), $station->name);
            return $this->sendResponse($trainStation->name);
        } catch (HafasException $e) {
            return $this->sendError([
                                        'id'      => 'HAFAS_EXCEPTION',
                                        'message' => $e->getMessage()
                                    ]);
        }
    }
}
