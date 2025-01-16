<?php

namespace App\Http\Controllers\API\v1;

use App\DataProviders\Hafas;
use App\Dto\Transport\Station as StationDto;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\Transport\StationController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Http\Resources\CheckinSuccessResource;
use App\Http\Resources\StationResource;
use App\Http\Resources\TripResource;
use App\Hydrators\CheckinRequestHydrator;
use App\Models\Station;
use App\Models\Status;
use App\Models\User;
use App\Notifications\YouHaveBeenCheckedIn;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class TransportController extends Controller
{
    /**
     * @param Request $request
     * @param int     $stationId
     *
     * @return JsonResponse
     * @todo: This endpoint needs to be restructured to use own Resources! Currently we just throw the raw db-rest response.
     *
     * @OA\Get(
     *      path="/station/{id}/departures",
     *      operationId="getDepartures",
     *      tags={"Checkin"},
     *      summary="Get departures from a station",
     *      description="Get departures from a station.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Träwelling-ID of the station (you can look this up with [trainStationAutocomplete](#/Checkin/trainStationAutocomplete))", required=true,
     *      ),
     *      @OA\Parameter(
     *          name="when",
     *          in="query",
     *          description="When to get the departures (default: now). If you omit the timezone, the datetime is interpreted as localtime. This is especially helpful when träwelling abroad.",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              format="date-time",
     *              example="2020-01-01T12:00:00.000Z"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="travelType",
     *          in="query",
     *          description="Means of transport (default: all)",
     *          required=false,
     *          @OA\Schema(
     *              ref="#/components/schemas/TravelType"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      externalDocs="https://v5.db.transport.rest/api.html#get-stopsiddepartures",
     *                      description="HAFAS Train model. This model might be subject to unexpected changes. See also external documentation at [https://v5.db.transport.rest/api.html#get-stopsiddepartures](https://v5.db.transport.rest/api.html#get-stopsiddepartures).",
     *                      example={
     *                          "tripId": "1|200513|0|81|6012023", "stop": { "type": "stop", "id": "8000191",
     *                          "name": "Karlsruhe Hbf", "location": { "type": "location", "id": "8000191", "latitude":
     *                          48.99353, "longitude": 8.401939 }, "products": { "nationalExpress": true, "national": true,
     *                          "regionalExp": true, "regional": true, "suburban": true, "bus": true, "ferry": false,
     *                          "subway": false, "tram": true, "taxi": true } }, "when": "2023-01-06T13:49:00+01:00",
     *                          "plannedWhen": "2023-01-06T13:49:00+01:00", "delay": null, "platform": "2",
     *                          "plannedPlatform": "2", "direction": "Zürich HB", "provenance": null, "line": { "type":
     *                          "line", "id": "ec-9", "fahrtNr": "9", "name": "EC 9", "public": true, "adminCode": "80____",
     *                          "productName": "EC", "mode": "train", "product": "national", "operator": { "type":
     *                          "operator", "id": "db-fernverkehr-ag", "name": "DB Fernverkehr AG" } }, "remarks": null,
     *                          "origin": null, "destination": { "type": "stop", "id": "8503000", "name": "Zürich HB",
     *                          "location": { "type": "location", "id": "8503000", "latitude": 47.378177, "longitude":
     *                          8.540211 }, "products": { "nationalExpress": true, "national": true, "regionalExp": true,
     *                          "regional": true, "suburban": true, "bus": true, "ferry": false, "subway": false, "tram":
     *                          true, "taxi": false } }, "currentTripPosition": { "type": "location", "latitude": 48.725382,
     *                          "longitude": 8.142888 }, "loadFactor": "high", "station": { "id": 5181, "ibnr": 8000191,
     *                          "rilIdentifier": "RK", "name": "Karlsruhe Hbf", "latitude": "48.993530", "longitude":
     *                          "8.401939" }
     *                      }
     *                 )
     *              ),
     *              @OA\Property(
     *                  property="meta",
     *                  type="object",
     *                  @OA\Property(
     *                      property="station",
     *                      ref="#/components/schemas/Station"
     *                  ),
     *                  @OA\Property(
     *                      property="times",
     *                      type="object",
     *                          @OA\Property(
     *                              property="now",
     *                              type="string",
     *                              format="date-time",
     *                              example="2020-01-01T12:00:00.000Z"
     *                          ),
     *                          @OA\Property(
     *                              property="prev",
     *                              type="string",
     *                              format="date-time",
     *                              example="2020-01-01T11:45:00.000Z"
     *                          ),
     *                          @OA\Property(
     *                              property="next",
     *                              type="string",
     *                              format="date-time",
     *                              example="2020-01-01T12:15:00.000Z"
     *                          )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=404, description="Station not found"),
     *      @OA\Response(response=422, description="Invalid input"),
     *      @OA\Response(response=502, description="Error with our data provider"),
     *      security={{"passport": {"create-statuses"}}, {"token": {}}}
     * )
     */
    public function getDepartures(Request $request, int $stationId): JsonResponse {
        $validated = $request->validate([
                                            'when'       => ['nullable', 'date'],
                                            'travelType' => ['nullable', new Enum(TravelType::class)],
                                        ]);

        $timestamp = isset($validated['when']) ? Carbon::parse($validated['when']) : now();
        $station   = Station::findOrFail($stationId);

        try {
            $departures = $this->dataProvider->getDepartures(
                station:   $station,
                when:      $timestamp,
                type:      TravelType::tryFrom($validated['travelType'] ?? null),
                localtime: isset($validated['when']) && !preg_match('(\+|Z)', $validated['when'])
            );

            $departures = $departures->sortBy(function($departure) {
                return $departure->when ?? $departure->plannedWhen;
            });

            return $this->sendResponse(
                data:       $departures->values(),
                additional: [
                                'meta' => [
                                    'station' => StationDto::fromModel($station),
                                    'times'   => [
                                        'now'  => $timestamp,
                                        'prev' => $timestamp->clone()->subMinutes(15),
                                        'next' => $timestamp->clone()->addMinutes(15)
                                    ],
                                ]
                            ]
            );
        } catch (HafasException) {
            return $this->sendError(__('messages.exception.generalHafas', [], 'en'), 502);
        } catch (ModelNotFoundException) {
            return $this->sendError(__('controller.transport.no-station-found', [], 'en'));
        } catch (Exception $exception) {
            report($exception);
            return $this->sendError('An unknown error occurred.', 500, null, $exception);
        }
    }

    /**
     * @OA\Get(
     *      path="/trains/trip",
     *      operationId="getTrainTrip",
     *      tags={"Checkin"},
     *      summary="Get the stopovers and trip information for a given train",
     *      @OA\Parameter(
     *          name="hafasTripId",
     *          in="query",
     *          description="HAFAS trip ID (fetched from departures)",
     *          example="1|323306|1|80|17072022",
     *          required=true
     *     ),
     *     @OA\Parameter(
     *          name="lineName",
     *          in="query",
     *          description="line name for that train",
     *          example="S 4",
     *          required=true
     *     ),
     *     @OA\Parameter(
     *          name="start",
     *          in="query",
     *          description="start point from where the stopovers should be desplayed",
     *          example=4711,
     *          required=true
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="int", example=1),
     *                  @OA\Property(property="category", ref="#/components/schemas/HafasTravelType"),
     *                  @OA\Property(property="number", type="string", example="4-a6s4-4"),
     *                  @OA\Property(property="lineName", type="string", example="S 4"),
     *                  @OA\Property(property="journeyNumber", type="int", example="34427"),
     *                  @OA\Property(property="origin", ref="#/components/schemas/Station"),
     *                  @OA\Property(property="destination", ref="#/components/schemas/Station"),
     *                  @OA\Property(property="stopovers", type="array",
     *                      @OA\Items(
     *                          ref="#/components/schemas/StopoverResource"
     *                      )
     *                  ),
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="No station found"),
     *       @OA\Response(response=503, description="There has been an error with our data provider"),
     *       security={
     *          {"passport": {"create-statuses"}}, {"token": {}}
     *       }
     *     )
     */
    public function getTrip(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'hafasTripId' => ['required', 'string'],
                                            'lineName'    => ['required', 'string'],
                                            'start'       => ['required', 'numeric', 'gt:0'],
                                        ]);

        try {
            $trip = TrainCheckinController::getHafasTrip(
                $validated['hafasTripId'],
                $validated['lineName'],
                (int) $validated['start']
            );
            return $this->sendResponse(data: new TripResource($trip));
        } catch (StationNotOnTripException) {
            return $this->sendError(__('controller.transport.not-in-stopovers', [], 'en'), 400);
        } catch (HafasException) {
            return $this->sendError(__('messages.exception.hafas.502', [], 'en'), 503);
        }
    }

    /**
     * @OA\Get(
     *      path="/trains/station/nearby",
     *      operationId="trainStationsNearby",
     *      tags={"Checkin"},
     *      summary="Location based search for stations",
     *      description="Returns the nearest station to the given coordinates",
     *      @OA\Parameter(
     *          name="latitude",
     *          in="query",
     *          description="latitude",
     *          example=48.991,
     *          required=true
     *     ),
     *     @OA\Parameter(
     *          name="longitude",
     *          in="query",
     *          description="longitude",
     *          example=8.4005,
     *          required=true
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Station"
     *                  )
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="No station found"),
     *       @OA\Response(response=503, description="There has been an error with our data provider"),
     *       security={
     *          {"passport": {"create-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     */
    public function getNextStationByCoordinates(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'latitude'  => ['required', 'numeric', 'min:-90', 'max:90'],
                                            'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
                                        ]);

        try {
            $nearestStation = $this->dataProvider->getNearbyStations(
                latitude:  $validated['latitude'],
                longitude: $validated['longitude'],
                results:   1
            )->first();
        } catch (HafasException) {
            $upperLeft  = [
                'latitude'  => $validated['latitude'] + 0.0015,
                'longitude' => $validated['longitude'] + 0.0015
            ];
            $lowerRight = [
                'latitude'  => $validated['latitude'] - 0.0015,
                'longitude' => $validated['longitude'] - 0.0015
            ];

            $nearestStation = Station::whereBetween('latitude', [$lowerRight['latitude'], $upperLeft['latitude']])
                                     ->whereBetween('longitude', [$lowerRight['longitude'], $upperLeft['longitude']])
                                     ->first();
        }

        if ($nearestStation === null) {
            return $this->sendError(__('controller.transport.no-station-found', [], 'en'));
        }

        return $this->sendResponse(new StationResource($nearestStation));
    }

    /**
     * @OA\Post(
     *      path="/trains/checkin",
     *      operationId="createCheckin",
     *      tags={"Checkin"},
     *      summary="Check in to a trip.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CheckinRequestBody")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CheckinSuccessResource")
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/CheckinForbiddenWithUsersResponse")),
     *       @OA\Response(response=409, description="Checkin collision"),
     *       security={
     *           {"passport": {"create-statuses"}}, {"token": {}}
     *       }
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse {
        $this->authorize('create', Status::class);

        $validated = $request->validate([
                                            'body'        => ['nullable', 'max:280'],
                                            'business'    => ['nullable', new Enum(Business::class)],
                                            'visibility'  => ['nullable', new Enum(StatusVisibility::class)],
                                            'eventId'     => ['nullable', 'integer', 'exists:events,id'],
                                            'toot'        => ['nullable', 'boolean'],
                                            'chainPost'   => ['nullable', 'boolean'],
                                            'ibnr'        => ['nullable', 'boolean'],
                                            'tripId'      => ['required'],
                                            'lineName'    => ['required'],
                                            'start'       => ['required', 'numeric'],
                                            'destination' => ['required', 'numeric'],
                                            'departure'   => ['required', 'date'],
                                            'arrival'     => ['required', 'date'],
                                            'force'       => ['nullable', 'boolean'],
                                            'with'        => ['nullable', 'array', 'max:10'],
                                        ]);
        if (isset($validated['with'])) {
            $withUsers      = User::whereIn('id', $validated['with'])->get();
            $forbiddenUsers = collect();
            foreach ($withUsers as $user) {
                if (!Auth::user()?->can('checkin', $user)) {
                    $forbiddenUsers->push($user);
                }
            }
            if ($forbiddenUsers->isNotEmpty()) {
                $forbiddenUserIds = $forbiddenUsers->pluck('id')->toArray();
                return response()->json(
                    data:   [
                                'message' => 'You are not allowed to check in the following users: ' . implode(',', $forbiddenUserIds),
                                'meta'    => [
                                    'invalidUsers' => $forbiddenUserIds
                                ]
                            ],
                    status: 403
                );
            }
        }

        try {
            $dto             = (new CheckinRequestHydrator($validated))->hydrateFromApi();
            $checkinResponse = TrainCheckinController::checkin($dto);

            // if isset, check in the other users with their default values
            foreach ($withUsers ?? [] as $user) {
                $dto->setUser($user);
                $dto->setBody(null);
                $dto->setStatusVisibility($user->default_status_visibility);
                $dto->setPostOnMastodonFlag(false);
                $checkin = TrainCheckinController::checkin($dto);
                $user->notify(new YouHaveBeenCheckedIn($checkin->status, auth()->user()));
                $checkinResponse->alsoOnThisConnection->push($checkin->status);
            }

            return $this->sendResponse(new CheckinSuccessResource($checkinResponse), 201);
        } catch (CheckInCollisionException $exception) {
            return $this->sendError([
                                        'status_id' => $exception->checkin->status_id,
                                        'lineName'  => $exception->checkin->trip->linename
                                    ], 409);

        } catch (StationNotOnTripException) {
            return $this->sendError('Given stations are not on the trip/have wrong departure/arrival.', 400);
        } catch (HafasException $exception) {
            return $this->sendError($exception->getMessage(), 400);
        } catch (AlreadyCheckedInException) {
            return $this->sendError(__('messages.exception.already-checkedin', [], 'en'), 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/station/{id}/home",
     *     operationId="setHomeStation",
     *     tags={"Checkin"},
     *     summary="Set a station as home station",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Träwelling-ID of the station",
     *         required=true,
     *         example=1234,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Station")
     *         ),
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Station not found"),
     *     @OA\Response(response=500, description="Unknown error"),
     *     security={{"passport": {"create-statuses"}}, {"token": {}}}
     * )
     * @param int $stationId
     *
     * @return JsonResponse
     */
    public function setHome(int $stationId): JsonResponse {
        try {
            $station = Station::findOrFail($stationId);

            auth()->user()?->update([
                                        'home_id' => $station->id
                                    ]);

            return $this->sendResponse(
                data: new StationResource($station),
            );
        } catch (ModelNotFoundException) {
            return $this->sendError('The station could not be found');
        } catch (Exception $exception) {
            report($exception);
            return $this->sendError('Unknown error', 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/trains/station/autocomplete/{query}",
     *      operationId="trainStationAutocomplete",
     *      tags={"Checkin"},
     *      summary="Autocomplete for stations",
     *      description="This request returns an array of max. 10 station objects matching the query. **CAUTION:** All
     *      slashes (as well as encoded to %2F) in {query} need to be replaced, preferrably by a space (%20)",
     * @OA\Parameter(
     *          name="query",
     *          in="path",
     *          description="station query",
     *          example="Karls"
     *     ),
     * @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/StationResource"
     *                  )
     *              )
     *          )
     *       ),
     * @OA\Response(response=401, description="Unauthorized"),
     * @OA\Response(response=503, description="There has been an error with our data provider"),
     *       security={
     *          {"passport": {"create-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     */
    public function getTrainStationAutocomplete(string $query): JsonResponse {
        try {
            $trainAutocompleteResponse = (new TransportBackend(Hafas::class))->getTrainStationAutocomplete($query);
            return $this->sendResponse($trainAutocompleteResponse);
        } catch (HafasException $e) {
            // check if app is in debug mode
            return $this->sendError(
                "There has been an error with our data provider",
                503,
                null,
                $e
            );
        }
    }

    /**
     * @OA\Get(
     *      path="/trains/station/history",
     *      operationId="trainStationHistory",
     *      tags={"Checkin"},
     *      summary="History for stations",
     *      description="This request returns an array of max. 10 most recent station objects that the user has arrived
     *      at.",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Station"
     *                  )
     *              )
     *          )
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       security={
     *          {"passport": {"create-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     */
    public function getTrainStationHistory(): AnonymousResourceCollection {
        return StationResource::collection(StationController::getLatestArrivals(auth()->user()));
    }
}
