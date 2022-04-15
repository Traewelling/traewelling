<?php

namespace App\Http\Controllers\API;

use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\Transport\HomeController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
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
            'travelType' => ['nullable', new Enum(TravelType::class)],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $validated = $validator->validate();

        try {
            $trainStationboardResponse = TransportBackend::getDepartures(
                stationQuery: $validated['station'],
                when:         isset($validated['when']) ? Carbon::parse($validated['when']) : null,
                travelType:   TravelType::tryFrom($validated['travelType'] ?? null),
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
            'tripID'      => ['required'],
            'lineName'    => ['nullable'], //Should be required in future API Releases due to DB Rest
            'start'       => ['required', 'numeric'],
            'destination' => ['required', 'numeric'],
            'body'        => ['nullable', 'max:280'],
            'tweet'       => ['nullable', 'boolean'],
            'toot'        => ['nullable', 'boolean'],
            //nullable, so that it is not a breaking change
            'departure'   => ['nullable', 'date'],
            'arrival'     => ['nullable', 'date'],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        $hafasTrip = HafasTrip::where('trip_id', $request->input('tripID'))->first();

        if ($hafasTrip === null && strlen($request->input('lineName')) == 0) {
            return $this->sendError('Please specify the trip with lineName.', 400);
        }

        if ($hafasTrip === null) {
            try {
                $hafasTrip = HafasController::getHafasTrip($request->input('tripID'), $request->input('lineName'));
            } catch (HafasException $exception) {
                return $this->sendError($exception->getMessage(), 400);
            }
        }

        try {
            $origin = TrainStation::where('ibnr', $request->input('start'))->first();
            if (isset($request->departure)) {
                $departure = Carbon::parse($request->input('departure'));
            } else {
                //Legacy: Get best matching timestamp from stopovers... it's just APIv0
                $departure = $hafasTrip->stopoversNEW->where('train_station_id', $origin->id)->first()->departure_planned;
            }
            $destination = TrainStation::where('ibnr', $request->input('destination'))->first();
            if (isset($request->arrival)) {
                $arrival = Carbon::parse($request->input('arrival'));
            } else {
                //Legacy: Get best matching timestamp from stopovers... it's just APIv0
                $arrival = $hafasTrip->stopoversNEW->where('train_station_id', $destination->id)->first()->arrival_planned;
            }

            $backendResponse = TrainCheckinController::checkin(
                user:           Auth::user(),
                hafasTrip:      $hafasTrip,
                origin:         $origin,
                departure:      $departure,
                destination:    $destination,
                arrival:        $arrival,
                body:           $request->input('body'),
                postOnTwitter:  isset($request->tweet) && $request->tweet,
                postOnMastodon: isset($request->toot) && $request->toot,
            );

            $trainCheckin = $backendResponse['status']->trainCheckin;

            return $this->sendResponse([
                                           'distance'             => $trainCheckin['distance'] / 1000,
                                           'duration'             => $trainCheckin['duration'],
                                           'statusId'             => $backendResponse['status']->id,
                                           'points'               => $trainCheckin['points'],
                                           'lineName'             => $trainCheckin['lineName'],
                                           'alsoOnThisConnection' => $trainCheckin['alsoOnThisConnection']
                                               ->map(function($status) {
                                                   return $status->user;
                                               })
                                       ]);

        } catch (CheckInCollisionException $exception) {
            return $this->sendError([
                                        'status_id' => $exception->getCollision()->status_id,
                                        'lineName'  => $exception->getCollision()->HafasTrip->first()->linename
                                    ], 409);
        } catch (StationNotOnTripException $exception) {
            report($exception);
            return $this->sendError('Given stations are not on the trip.', 400);
        } catch (AlreadyCheckedInException) {
            return $this->sendError(__('messages.exception.already-checkedin', [], 'en'), 400);
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

    public function setHome(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'ibnr' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        try {
            $station      = HafasController::getTrainStation($request->ibnr); //Workaround to support APIv1
            $trainStation = HomeController::setHome(Auth::user(), $station);
            return $this->sendResponse($trainStation->name);
        } catch (HafasException $e) {
            return $this->sendError([
                                        'id'      => 'HAFAS_EXCEPTION',
                                        'message' => $e->getMessage()
                                    ]);
        }
    }
}
