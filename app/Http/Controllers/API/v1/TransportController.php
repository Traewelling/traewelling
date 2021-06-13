<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\TravelType;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\TransportController as TransportBackend;
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
}
