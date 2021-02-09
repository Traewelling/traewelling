<?php

namespace App\Http\Controllers\API;

use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\HafasTrip;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Class TransportController
 * @group Trains
 * This category handles the search of trainstations, train departures, line runs and the creation of train check ins.
 * @package App\Http\Controllers\API
 */
class TransportController extends ResponseController
{
    /**
     * Autocomplete
     * This endpoint can be called multiple times in succession when searching stations by name to provide suggestions
     * for the user to select from. Please provide at least 3 characters when retrieving suggestions. Otherwise,
     * only call this endpoint with less than 3 characters if the user explicitly requested a search.
     *
     * @group Trains
     * @urlParam station string required String to be searched for in the stations Example: Kar
     * @response 200 [
     * {
     * "ibnr": "8079041",
     * "name": "Karlsruhe Bahnhofsvorplatz",
     * "provider": "train"
     * }
     * ]
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     * @param $station
     * @return JsonResponse
     */
    public function TrainAutocomplete($station) {
        $trainAutocompleteResponse = TransportBackend::TrainAutocomplete($station);
        return $this->sendResponse($trainAutocompleteResponse);
    }

    /**
     * Stationboard
     * Returns the trains that will depart from a station in the near future or at a specific point in time.
     * Responses can be filtered for types of public transport e.g. busses, regional and national trains.
     *
     * @group Trains
     * @queryParam station string required The name of the train station Example: Karlsruhe
     * @queryParam when date nullable Timestamp of the query Example: 2019-12-01T21:03:00+01:00
     * @queryParam travelType string nullable Must be one of the following: 'nationalExpress', 'express', 'regionalExp', 'regional', 'suburban', 'bus', 'ferry', 'subway', 'tram', 'taxi' Example: express
     * @responseFile status=200 storage/responses/v0/stationboard.get.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function TrainStationboard(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'station'    => ['required', 'string'],
            'when'       => ['nullable', 'date'],
            'travelType' => ['nullable', Rule::in([
                                                      'nationalExpress', 'express', 'regionalExp', 'regional',
                                                      'suburban', 'bus', 'ferry', 'subway', 'tram', 'taxi'
                                                  ])]
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

    /**
     * Train trip
     * Returns the stopovers and other details of a specific train.
     *
     * @queryParam tripID string required The given ID of the trip. Example: 1|1937395|17|80|24112019
     * @queryParam lineName string required The given name of the line. Example: 62
     * @queryParam start int required The IBNR of the starting point of the train. Example: 8079041
     * @responseFile status=200 storage/responses/v0/trainTrip.get.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Check in
     * Creates a check in for a train
     *
     * @queryParam tripID string required ID of the to-be-ckecked-in trip. Example: 1|1937395|17|80|24112019
     * @queryParam lineName string required ID of the to-be-checked-in trip. Example: 62
     * @queryParam start int required The IBNR of the starting point of the train. Example: 8079041
     * @queryParam destination int required The IBNR of the destination. Example: 8079041
     * @queryParam body string max:280 The body of the status. Example: This is my first Check-in!
     * @queryParam tweet boolean Should this post be tweeted? Example: true
     * @queryParam toot boolean Should this post be posted to mastodon? Example: false
     *
     * @responseFile status=200 scenario="Successfully checked in" storage/responses/v0/trains.checkin.post.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param Request $request
     * @return JsonResponse
     */
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

        } catch (Throwable $e) {
            return $this->sendError('Unknown Error occured', 500);
        }

    }

    /**
     * Latest train stations
     * Retrieves the last 5 station the logged in user arrived at
     *
     * @response 200 [
     * {
     * "id": 3,
     * "ibnr": "8079041",
     * "name": "Karlsruhe Bahnhofsvorplatz",
     * "latitude": 48.994348,
     * "longitude": 48.994348
     * }
     * ]
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @return JsonResponse
     */
    public function TrainLatestArrivals() {
        $arrivals = TransportBackend::getLatestArrivals(auth()->user());

        return $this->sendResponse($arrivals);
    }

    /**
     * Stations nearby
     * Searches for nearby train stations
     *
     * @queryParam latitude float required min:-180, max:180 Example:48.994348
     * @queryParam longitude float required min:-180, max:180 Example:48.994348
     * @response 200 {
     * "type": "station",
     * "id": "8000191",
     * "name": "Karlsruhe Hbf",
     * "location": {
     * "type": "location",
     * "id": "8079041",
     * "latitude": 48.994348,
     * "longitude": 8.399583
     * },
     * "products": {
     * "nationalExpress": true,
     * "national": true,
     * "regionalExp": true,
     * "regional": true,
     * "suburban": true,
     * "bus": true,
     * "ferry": true,
     * "subway": true,
     * "tram": true,
     * "taxi": true
     * }
     * }
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param Request $request
     * @return JsonResponse
     * @throws HafasException
     */
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

    /**
     * Home Station
     * Gets the home station of the logged in user
     *
     * @response 200 {
     * "id": 3,
     * "ibnr": "8079041",
     * "name": "Karlsruhe Bahnhofsvorplatz",
     * "latitude": 48.994348,
     * "longitude": 48.994348
     * }
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @return JsonResponse
     */
    public function getHome() {
        $home = auth()->user()->home;
        if ($home === null) {
            return $this->sendError('user has not set a home station.');
        }
        return $this->sendResponse($home);
    }

    /**
     * Home Station
     * Sets the home station for the logged in user
     *
     * @queryParam ibnr int required Example: 8123
     *
     * @response 200 "Ost.Punkt 812 km"
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setHome(Request $request) {
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
