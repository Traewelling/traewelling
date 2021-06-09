<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\TravelType;
use App\Exceptions\HafasException;
use App\Exceptions\MissingParametersExection;
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
        } catch (HafasException $exception) {
            return $this->sendError(400, $exception->getMessage());
        } catch (ModelNotFoundException) {
            return $this->sendError(404, __('controller.transport.no-station-found'));
        }

        return $this->sendv1Response(
            data: $trainStationboardResponse['departures'],
            additional: ["meta" => ['station' => $trainStationboardResponse['station'],
                                    'times'   => $trainStationboardResponse['times'],
                ]]
        );

    }
}
