<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\HafasTrip;
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


    public function create(Request $request) {
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

        } catch (StationNotOnTripException) {
            return $this->sendError('Given stations are not on the trip.', 400);
        } catch (Throwable $exception) {
            report($exception);
            return $this->sendError('Unknown Error occured', 500);
        }

    }

}
