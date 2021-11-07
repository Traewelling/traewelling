<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Http\Resources\TrainStationResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransportController extends ResponseController
{
    /**
     * @param Request $request
     * @param string  $name
     *
     * @return JsonResponse
     * @throws ValidationException
     * @see All slashes (as well as encoded to %2F) in $name need to be replaced, preferrably by a spache (%20)
     */
    public function departures(Request $request, string $name): JsonResponse {
        $validated = $request->validate([
                                            'when'       => ['nullable', 'date'],
                                            'travelType' => ['nullable', Rule::in(TravelType::getList())]
                                        ]);

        try {
            $trainStationboardResponse = TransportBackend::getDepartures(
                $name,
                isset($validated['when']) ? Carbon::parse($validated['when']) : null,
                $validated['travelType'] ?? null
            );
        } catch (HafasException) {
            return $this->sendv1Error("There has been an error with our data provider", 400);
        } catch (ModelNotFoundException) {
            return $this->sendv1Error("Your query matches no station", 404);
        }

        return $this->sendv1Response(
            data: $trainStationboardResponse['departures'],
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
                                            'business'    => ['nullable', Rule::in(Business::getList())],
                                            'visibility'  => ['nullable', Rule::in(StatusVisibility::getList())],
                                            'eventId'     => ['nullable', 'integer', 'exists:events,id'],
                                            'tweet'       => ['nullable', 'boolean'],
                                            'toot'        => ['nullable', 'boolean'],
                                            'ibnr'        => ['nullable', 'boolean'],
                                            'tripId'      => 'required',
                                            'lineName'    => 'required',
                                            'start'       => ['required', 'numeric'],
                                            'destination' => ['required', 'numeric'],
                                            'departure'   => ['required', 'date'],
                                            'arrival'     => ['required', 'date'],
                                        ]);

        try {
            $status = StatusBackend::createStatus(
                user:       auth()->user(),
                business:   $validated['business'] ?? 0,
                visibility: $validated['visibility'] ?? StatusVisibility::PUBLIC,
                body:       $validated['body'] ?? null,
                eventId:    $validated['eventId'] ?? null
            );

            $hafasTrip = HafasController::getHafasTrip($validated['tripId'], $validated['lineName']);

            $trainCheckinResponse = TransportBackend::createTrainCheckin(
                status:    $status,
                trip:      $hafasTrip,
                entryStop: $validated['start'],
                exitStop:  $validated['destination'],
                departure: Carbon::parse($validated['departure']),
                arrival:   Carbon::parse($validated['arrival']),
                ibnr:      $validated['ibnr'] ?? false
            );

            if ($validated['tweet'] && auth()->user()?->socialProfile?->twitter_id != null) {
                TransportBackend::postTwitter($status);
            }
            if ($validated['toot'] && auth()->user()?->socialProfile?->mastodon_id != null) {
                TransportBackend::postMastodon($status);
            }

            return $this->sendv1Response($trainCheckinResponse);
        } catch (CheckInCollisionException $e) {
            $status?->delete();
            return $this->sendv1Error([
                                        'status_id' => $e->getCollision()->status_id,
                                        'lineName'  => $e->getCollision()->HafasTrip->first()->linename
                                    ], 409);

        } catch (StationNotOnTripException) {
            $status?->delete();
            return $this->sendv1Error('Given stations are not on the trip/have wrong departure/arrival.', 400);
        } catch (HafasException $exception) {
            $status?->delete();
            return $this->sendv1Error($exception->getMessage(), 400);
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
            $station = TransportBackend::setTrainHome(user: auth()->user(), stationName: $stationName);
        } catch (HafasException) {
            return $this->sendv1Error("There has been an error with our data provider", 400);
        } catch (ModelNotFoundException) {
            return $this->sendv1Error("Your query matches no station", 404);
        }

        return $this->sendv1Response(
            data: new TrainStationResource($station),
        );
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
