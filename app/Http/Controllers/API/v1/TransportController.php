<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\NotConnectedException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\Transport\HomeController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Http\Resources\HafasTripResource;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TrainStationResource;
use App\Models\Event;
use App\Models\TrainStation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class TransportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/trains/station/{name}/departures",
     *     operationId="getDepartures",
     *     tags={"Checkin"},
     *     summary="Get departures from a station",
     *     description="Get departures from a station",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of the station (replace slashes with spaces)",
     *         required=true,
     *     ),
     *     @OA\Parameter(
     *         name="when",
     *         in="query",
     *         description="When to get the departures (default: now)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date-time",
     *             example="2020-01-01T12:00:00.000Z"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="travelType",
     *         in="query",
     *         description="Means of transport (default: all)",
     *         required=false,
     *         @OA\Schema(
     *          ref="#/components/schemas/TravelTypeEnum"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     externalDocs="https://v5.db.transport.rest/api.html#get-stopsiddepartures",
     *                     description="HAFAS Train model. This model might be subject to unexpected changes. See also
     *                     external documentation at
     *                     [https://v5.db.transport.rest/api.html#get-stopsiddepartures](https://v5.db.transport.rest/api.html#get-stopsiddepartures).",
     *                     example={ "tripId": "1|200513|0|81|6012023", "stop": { "type": "stop", "id": "8000191",
     *                     "name": "Karlsruhe Hbf", "location": { "type": "location", "id": "8000191", "latitude":
     *                     48.99353, "longitude": 8.401939 }, "products": { "nationalExpress": true, "national": true,
     *                     "regionalExp": true, "regional": true, "suburban": true, "bus": true, "ferry": false,
     *                     "subway": false, "tram": true, "taxi": true } }, "when": "2023-01-06T13:49:00+01:00",
     *                     "plannedWhen": "2023-01-06T13:49:00+01:00", "delay": null, "platform": "2",
     *                     "plannedPlatform": "2", "direction": "Zürich HB", "provenance": null, "line": { "type":
     *                     "line", "id": "ec-9", "fahrtNr": "9", "name": "EC 9", "public": true, "adminCode": "80____",
     *                     "productName": "EC", "mode": "train", "product": "national", "operator": { "type":
     *                     "operator", "id": "db-fernverkehr-ag", "name": "DB Fernverkehr AG" } }, "remarks": null,
     *                     "origin": null, "destination": { "type": "stop", "id": "8503000", "name": "Zürich HB",
     *                     "location": { "type": "location", "id": "8503000", "latitude": 47.378177, "longitude":
     *                     8.540211 }, "products": { "nationalExpress": true, "national": true, "regionalExp": true,
     *                     "regional": true, "suburban": true, "bus": true, "ferry": false, "subway": false, "tram":
     *                     true, "taxi": false } }, "currentTripPosition": { "type": "location", "latitude": 48.725382,
     *                     "longitude": 8.142888 }, "loadFactor": "high", "station": { "id": 5181, "ibnr": 8000191,
     *                     "rilIdentifier": "RK", "name": "Karlsruhe Hbf", "latitude": "48.993530", "longitude":
     *                     "8.401939" } }
     *                 )
     *            ),
     *            @OA\Property(
     *              property="meta",
     *              type="object",
     *              @OA\Property(
     *                  property="station",
     *                  ref="#/components/schemas/TrainStation"
     *              ),
     *              @OA\Property(
     *                  property="times",
     *                 type="object",
     *                 @OA\Property(
     *                     property="now",
     *                     type="string",
     *                     format="date-time",
     *                     example="2020-01-01T12:00:00.000Z"
     *                ),
     *                @OA\Property(
     *                    property="prev",
     *                    type="string",
     *                    format="date-time",
     *                    example="2020-01-01T11:45:00.000Z"
     *               ),
     *               @OA\Property(
     *                   property="next",
     *                   type="string",
     *                   format="date-time",
     *                   example="2020-01-01T12:15:00.000Z"
     *              )
     *         )
     *         )
     *        )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Station not found",
     *     ),
     *     @OA\Response(
     *         response=502,
     *         description="Error with our data provider",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid input",
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     security={
     *        {"passport": {"create-statuses"}}, {"token": {}}
     *
     *     }
     * )
     *
     * @param Request $request
     * @param string  $name
     *
     * @return JsonResponse
     * @see All slashes (as well as encoded to %2F) in $name need to be replaced, preferrably by a space (%20)
     */
    public function departures(Request $request, string $name): JsonResponse {
        $validated = $request->validate([
                                            'when'       => ['nullable', 'date'],
                                            'travelType' => ['nullable', new Enum(TravelType::class)],
                                        ]);

        try {
            $trainStationboardResponse = TransportBackend::getDepartures(
                stationQuery: $name,
                when:         isset($validated['when']) ? Carbon::parse($validated['when']) : null,
                travelType:   TravelType::tryFrom($validated['travelType'] ?? null),
            );
        } catch (HafasException) {
            return $this->sendError(__('messages.exception.generalHafas', [], 'en'), 502);
        } catch (ModelNotFoundException) {
            return $this->sendError(__('controller.transport.no-station-found', [], 'en'));
        }

        return $this->sendResponse(
            data:       $trainStationboardResponse['departures'],
            additional: ["meta" => ['station' => $trainStationboardResponse['station'],
                                    'times'   => $trainStationboardResponse['times'],
                        ]]
        );
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
     *                  @OA\Property(property="id", type="int64", example=1),
     *                  @OA\Property(property="category", ref="#/components/schemas/TrainCategoryEnum"),
     *                  @OA\Property(property="number", type="string", example="4-a6s4-4"),
     *                  @OA\Property(property="lineName", type="string", example="S 4"),
     *                  @OA\Property(property="journeyNumber", type="int64", example="34427"),
     *                  @OA\Property(property="origin", ref="#/components/schemas/TrainStation"),
     *                  @OA\Property(property="destination", ref="#/components/schemas/TrainStation"),
     *                  @OA\Property(property="stopovers", type="array",
     *                      @OA\Items(
     *                          ref="#/components/schemas/TrainStopover"
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
                                            'tripId'      => ['required_without:hafasTripId', 'string'], //ToDo deprecated: remove after 2023-02-28
                                            'hafasTripId' => ['required_without:tripId', 'string'],
                                            'lineName'    => ['required', 'string'],
                                            'start'       => ['required', 'numeric', 'gt:0'],
                                        ]);

        try {
            $hafasTrip = TrainCheckinController::getHafasTrip(
                $validated['hafasTripId'] ?? $validated['tripId'], //ToDo deprecated: change to hafasTripId after 2023-02-28
                $validated['lineName'],
                (int) $validated['start']
            );
            return $this->sendResponse(data: new HafasTripResource($hafasTrip));
        } catch (StationNotOnTripException) {
            return $this->sendError(__('controller.transport.not-in-stopovers', [], 'en'), 400);
        }
    }

    /**
     * @OA\Get(
     *      path="/trains/station/nearby",
     *      operationId="trainStationsNearby",
     *      tags={"Checkin"},
     *      summary="Location based search for trainstations",
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
     *                      ref="#/components/schemas/TrainStation"
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
            $nearestStation = HafasController::getNearbyStations(
                latitude:  $validated['latitude'],
                longitude: $validated['longitude'],
                results:   1
            )->first();
        } catch (HafasException) {
            return $this->sendError(__('messages.exception.generalHafas', [], 'en'), 503);
        }

        if ($nearestStation === null) {
            return $this->sendError(__('controller.transport.no-station-found', [], 'en'));
        }

        return $this->sendResponse(new TrainStationResource($nearestStation));
    }

    /**
     * @OA\Post(
     *      path="/trains/checkin",
     *      operationId="createTrainCheckin",
     *      tags={"Checkin"},
     *      summary="Create a checkin",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/TrainCheckinRequestBody")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/TrainCheckinResponse")
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=409, description="Checkin collision"),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       security={
     *           {"passport": {"create-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws NotConnectedException
     */
    public function create(Request $request): JsonResponse {
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
                                            'force'       => ['nullable', 'boolean']
                                        ]);

        try {
            $searchKey          = isset($validated['ibnr']) ? 'ibnr' : 'id';
            $originStation      = TrainStation::where($searchKey, $validated['start'])->first();
            $destinationStation = TrainStation::where($searchKey, $validated['destination'])->first();

            $trainCheckinResponse           = TrainCheckinController::checkin(
                user:           Auth::user(),
                hafasTrip:      HafasController::getHafasTrip($validated['tripId'], $validated['lineName']),
                origin:         $originStation,
                departure:      Carbon::parse($validated['departure']),
                destination:    $destinationStation,
                arrival:        Carbon::parse($validated['arrival']),
                travelReason:   Business::tryFrom($validated['business'] ?? Business::PRIVATE->value),
                visibility:     StatusVisibility::tryFrom($validated['visibility'] ?? StatusVisibility::PUBLIC->value),
                body:           $validated['body'] ?? null,
                event:          isset($validated['eventId']) ? Event::find($validated['eventId']) : null,
                force:          isset($validated['force']) && $validated['force'],
                postOnMastodon: isset($validated['toot']) && $validated['toot'],
                shouldChain:    isset($validated['chainPost']) && $validated['chainPost']
            );
            $trainCheckinResponse['status'] = new StatusResource($trainCheckinResponse['status']);

            //Rewrite ['points'] so the DTO will match the documented structure -> non-breaking api change
            $pointsCalculation              = $trainCheckinResponse['points'];
            $trainCheckinResponse['points'] = [
                'points'      => $pointsCalculation->points,
                'calculation' => [
                    'base'     => $pointsCalculation->basePoints,
                    'distance' => $pointsCalculation->distancePoints,
                    'factor'   => $pointsCalculation->factor,
                    'reason'   => $pointsCalculation->reason->value,
                ],
                'additional'  => null, //unused old attribute (not removed so this isn't breaking)
            ];

            return $this->sendResponse($trainCheckinResponse, 201); //ToDo: Check if documented structure has changed
        } catch (CheckInCollisionException $exception) {
            return $this->sendError([
                                        'status_id' => $exception->getCollision()->status_id,
                                        'lineName'  => $exception->getCollision()->HafasTrip->linename
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
     *     path="/trains/station/{name}/home",
     *     operationId="setHomeStation",
     *     tags={"Checkin"},
     *     summary="Set a station as home station",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of the station",
     *         required=true,
     *         example="Karlsruhe Hbf",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/TrainStation")
     *         ),
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Station not found"),
     *     @OA\Response(response=502, description="Error with our data provider"),
     *     security={
     *           {"passport": {"create-statuses"}}, {"token": {}}
     *
     *       }
     * )
     * @param string $stationName
     *
     * @return JsonResponse
     * @see All slashes (as well as encoded to %2F) in $name need to be replaced, preferrably by a space (%20)
     */
    public function setHome(string $stationName): JsonResponse {
        try {
            $trainStation = HafasController::getStations(query: $stationName, results: 1)->first();
            if ($trainStation === null) {
                return $this->sendError("Your query matches no station");
            }

            $station = HomeController::setHome(user: auth()->user(), trainStation: $trainStation);

            return $this->sendResponse(
                data: new TrainStationResource($station),
            );
        } catch (HafasException) {
            return $this->sendError("There has been an error with our data provider", 502);
        } catch (ModelNotFoundException) {
            return $this->sendError("Your query matches no station");
        }
    }

    /**
     * @OA\Get(
     *      path="/trains/station/autocomplete/{query}",
     *      operationId="trainStationAutocomplete",
     *      tags={"Checkin"},
     *      summary="Autocomplete for trainstations",
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
     *                      ref="#/components/schemas/ShortTrainStation"
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
            $trainAutocompleteResponse = TransportBackend::getTrainStationAutocomplete($query);
            return $this->sendResponse($trainAutocompleteResponse);
        } catch (HafasException) {
            return $this->sendError("There has been an error with our data provider", 503);
        }
    }

    /**
     * @OA\Get(
     *      path="/trains/station/history",
     *      operationId="trainStationHistory",
     *      tags={"Checkin"},
     *      summary="History for trainstations",
     *      description="This request returns an array of max. 10 most recent station objects that the user has arrived
     *      at.",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/TrainStation"
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
        return TrainStationResource::collection(TransportBackend::getLatestArrivals(auth()->user()));
    }
}
