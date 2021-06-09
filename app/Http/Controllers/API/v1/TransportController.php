<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\TravelType;
use App\Exceptions\HafasException;
use App\Exceptions\MissingParametersExection;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\TransportController as TransportBackend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransportController extends ResponseController
{
    public function departures(Request $request, string $name) {
        $validator = Validator::make($request->all(), [
            'when'       => ['nullable', 'date'],
            'travelType' => ['nullable', Rule::in(TravelType::getList())]
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $validated = $validator->validate();

        try {
            $trainStationboardResponse = TransportBackend::TrainStationboard(
                $name,
                isset($validated['when']) ? Carbon::parse($validated['when']) : null,
                $validated['travelType'] ?? null
            );
        } catch (HafasException $exception) {
            return $this->sendError(400, $exception->getMessage());
        } catch (MissingParametersExection) {
            return $this->sendError(400, __('controller.transport.no-name-given'));
        }
        if ($trainStationboardResponse === null) {

            return $this->sendError(404, __('controller.transport.no-station-found'));
        }

        return $this->sendv1Response([
                                       'station'    => $trainStationboardResponse['station'],
                                       'when'       => $trainStationboardResponse['when'],
                                       'departures' => $trainStationboardResponse['departures']
                                   ]);

    }
}
