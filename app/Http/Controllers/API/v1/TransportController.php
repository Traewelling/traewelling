<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\Transport\HomeController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController as TransportBackend;
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

class TransportController extends ResponseController
{
    /**
     * @param Request $request
     * @param string  $name
     *
     * @return JsonResponse
     * @see All slashes (as well as encoded to %2F) in $name need to be replaced, preferrably by a spache (%20)
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
            return $this->sendv1Error("There has been an error with our data provider", 400);
        } catch (ModelNotFoundException) {
            return $this->sendv1Error("Your query matches no station", 404);
        }

        return $this->sendv1Response(
            data:       $trainStationboardResponse['departures'],
            additional: ["meta" => ['station' => $trainStationboardResponse['station'],
                                    'times'   => $trainStationboardResponse['times'],
                        ]]
        );
    }

    public function getTrip(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'tripId'   => 'required',
                                            'lineName' => 'required',
                                            'start'    => 'required'
                                        ]);

        try {
            $trainTripResponse = TransportBackend::getTrainTrip(
                $validated['tripId'],
                $validated['lineName'],
                $validated['start']
            );
        } catch (StationNotOnTripException) {
            return $this->sendv1Error(__('controller.transport.not-in-stopovers'), 400);
        }

        return $this->sendv1Response(data: $trainTripResponse);
    }

    public function getNextStationByCoordinates(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'latitude'  => ['required', 'numeric', 'min:-90', 'max:90'],
                                            'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
                                            'limit'     => ['nullable', 'numeric', 'min:1', 'max:20']
                                        ]);

        try {
            $nearestStation = HafasController::getNearbyStations(
                latitude:  $validated['latitude'],
                longitude: $validated['longitude'],
                results:   $validated['limit'] ?? 1
            )->first();
        } catch (HafasException) {
            return $this->sendv1Error(__('messages.exception.generalHafas'), 503);
        }

        if ($nearestStation === null) {
            return $this->sendv1Error(__('controller.transport.no-station-found'));
        }

        return $this->sendv1Response(new TrainStationResource($nearestStation));
    }

    public function create(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'body'        => ['nullable', 'max:280'],
                                            'business'    => ['nullable', new Enum(Business::class)],
                                            'visibility'  => ['nullable', new Enum(StatusVisibility::class)],
                                            'eventId'     => ['nullable', 'integer', 'exists:events,id'],
                                            'tweet'       => ['nullable', 'boolean'],
                                            'toot'        => ['nullable', 'boolean'],
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
                postOnTwitter:  isset($validated['tweet']) && $validated['tweet'],
                postOnMastodon: isset($validated['toot']) && $validated['toot'],
            );
            $trainCheckinResponse['status'] = new StatusResource($trainCheckinResponse['status']);
            return $this->sendv1Response($trainCheckinResponse);
        } catch (CheckInCollisionException $exception) {
            return $this->sendv1Error([
                                          'status_id' => $exception->getCollision()->status_id,
                                          'lineName'  => $exception->getCollision()->HafasTrip->first()->linename
                                      ], 409);

        } catch (StationNotOnTripException) {
            return $this->sendv1Error('Given stations are not on the trip/have wrong departure/arrival.', 400);
        } catch (HafasException $exception) {
            return $this->sendv1Error($exception->getMessage(), 400);
        } catch (AlreadyCheckedInException) {
            return $this->sendv1Error(__('messages.exception.already-checkedin', [], 'en'), 400);
        } catch (TrainCheckinAlreadyExistException) {
            return $this->sendv1Error('CheckIn already exists', 409);
        }
    }

    /**
     * @param string $stationName
     *
     * @return JsonResponse
     * @see All slashes (as well as encoded to %2F) in $name need to be replaced, preferrably by a spache (%20)
     */
    public function setHome(string $stationName): JsonResponse {
        try {
            $trainStation = HafasController::getStations(query: $stationName, results: 1)->first();
            if ($trainStation === null) {
                return $this->sendv1Error("Your query matches no station");
            }

            $station = HomeController::setHome(user: auth()->user(), trainStation: $trainStation);

            return $this->sendv1Response(
                data: new TrainStationResource($station),
            );
        } catch (HafasException) {
            return $this->sendv1Error("There has been an error with our data provider", 400);
        } catch (ModelNotFoundException) {
            return $this->sendv1Error("Your query matches no station", 404);
        }
    }

    /**
     * @param string $query
     *
     * @return JsonResponse
     * @see All slashes (as well as encoded to %2F) in $query need to be replaced, preferrably by a spache (%20)
     */
    public function getTrainStationAutocomplete(string $query): JsonResponse {
        try {
            $trainAutocompleteResponse = TransportBackend::getTrainStationAutocomplete($query);
            return $this->sendv1Response($trainAutocompleteResponse);
        } catch (HafasException) {
            return $this->sendv1Error("There has been an error with our data provider", 400);
        }
    }

    public function getTrainStationHistory(): AnonymousResourceCollection {
        return TrainStationResource::collection(TransportBackend::getLatestArrivals(auth()->user()));
    }
}
