<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController as TransportBackend;
use \App\Http\Controllers\StatusController as StatusBackend;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransportController extends ResponseController
{
    public function departures(Request $request, string $name): JsonResponse {
        $validator = Validator::make($request->all(), [
            'when'       => ['nullable', 'date'],
            'travelType' => ['nullable', Rule::in(TravelType::getList())]
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $validated = $validator->validate();

        try {
            $trainStationboardResponse = TransportBackend::getDepartures(
                $name,
                isset($validated['when']) ? Carbon::parse($validated['when']) : null,
                $validated['travelType'] ?? null
            );
        } catch (HafasException) {
            return $this->sendError("There has been an error with our data provider", 400);
        } catch (ModelNotFoundException) {
            return $this->sendError("Your query matches no station", 404);
        }

        return $this->sendv1Response(
            data: $trainStationboardResponse['departures'],
            additional: ["meta" => ['station' => $trainStationboardResponse['station'],
                                    'times'   => $trainStationboardResponse['times'],
                ]]
        );

    }

    public function getTrip(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'tripID'   => 'required',
            'lineName' => 'required',
            'start'    => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        try {
            $trainTripResponse = TransportBackend::getTrainTrip(
                $request->tripID,
                $request->lineName,
                $request->start
            );
        } catch (StationNotOnTripException) {
            return $this->sendError(__('controller.transport.not-in-stopovers'), 400);
        }

        return $this->sendv1Response(data: $trainTripResponse);
    }


    public function create(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'body'        => ['nullable', 'max:280'],
            'business'    => ['nullable', Rule::in(Business::getList())],
            'visibility'  => ['nullable', Rule::in(StatusVisibility::getList())],
            'eventID'     => ['nullable', 'integer', 'exists:events,id'],
            'tweet'       => ['nullable', 'boolean'],
            'toot'        => ['nullable', 'boolean'],
            'ibnr'        => ['nullable', 'boolean'],
            'tripID'      => 'required',
            'lineName'    => 'required',
            'start'       => ['required', 'numeric'],
            'destination' => ['required', 'numeric'],
            'departure'   => ['required', 'date'],
            'arrival'     => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        try {
            $status = StatusBackend::createStatus(
                user: auth()->user(),
                business: $request->input('business') ?? 0,
                visibility: $request->input('visibility') ?? StatusVisibility::PUBLIC,
                body: $request->input('body'),
                eventId: $request->input('eventID')
            );

            $hafasTrip = HafasController::getHafasTrip($request->input('tripID'), $request->input('lineName'));

            $trainCheckinResponse = TransportBackend::createTrainCheckin(
                status: $status,
                trip: $hafasTrip,
                entryStop: $request->input('start'),
                exitStop: $request->input('destination'),
                departure: Carbon::parse($request->input('departure')),
                arrival: Carbon::parse($request->input('arrival')),
                ibnr: $request->input('ibnr') ?? false
            );

            if ($request->input('tweet')) {
                TransportBackend::postTwitter($status);
            }
            if ($request->input('toot')) {
                TransportBackend::postMastodon($status);
            }

            $trainCheckinResponse['alsoOnThisConnection']->map(function($status) {
                if ($status?->user) {
                    return $status->user;
                }
            });

            return $this->sendv1Response($trainCheckinResponse);
        } catch (CheckInCollisionException $e) {
            $status?->delete();
            return $this->sendError([
                                        'status_id' => $e->getCollision()->status_id,
                                        'lineName'  => $e->getCollision()->HafasTrip->first()->linename
                                    ], 409);

        } catch (StationNotOnTripException) {
            $status?->delete();
            return $this->sendError('Given stations are not on the trip/have wrong departure/arrival.', 400);
        } catch (HafasException $exception) {
            $status?->delete();
            return $this->sendError($exception->getMessage(), 400);
        }
    }
}
