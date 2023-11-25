<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Enum\HafasTravelType;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Controllers\Backend\Transport\ManualTripCreator as TripBackend;
use App\Models\HafasOperator;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class TripController
{

    public function renderTrip(string $id): View {
        $trip = HafasTrip::with(['checkins'])
                         ->where('trip_id', $id)
                         ->firstOrFail();
        return view('admin.trip.show', [
            'trip' => $trip
        ]);
    }

    public function createTrip(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'origin'         => ['required', 'numeric', 'exists:train_stations,ibnr'],
                                            'departure'      => ['required'],
                                            'destination'    => ['required', 'numeric', 'exists:train_stations,ibnr'],
                                            'arrival'        => ['required'],
                                            'operator_id'    => ['nullable', 'numeric', 'exists:hafas_operators,id'],
                                            'category'       => ['required', new Enum(HafasTravelType::class)],
                                            'number'         => ['required', 'string'],
                                            'linename'       => ['required', 'string'],
                                            'journey_number' => ['required', 'numeric'],
                                        ]);

        $creator = new ManualTripCreator();

        $creator->category                  = HafasTravelType::from($validated['category']);
        $creator->lineName                  = $validated['linename'];
        $creator->journeyNumber             = $validated['journey_number'];
        $creator->operator                  = HafasOperator::find($validated['operator_id']);
        $creator->origin                    = TrainStation::where('ibnr', $validated['origin'])->firstOrFail();
        $creator->originDeparturePlanned    = Carbon::parse(str_contains($validated['departure'], '+') && str_contains($validated['departure'], '-')
                                                                ? $validated['departure'] : $validated['departure'] . '+00:00');
        $creator->destination               = TrainStation::where('ibnr', $validated['destination'])->firstOrFail();
        $creator->destinationArrivalPlanned = Carbon::parse(str_contains($validated['arrival'], '+') && str_contains($validated['arrival'], '-')
                                                                ? $validated['arrival'] : $validated['arrival'] . '+00:00');

        $trip = $creator->createTrip();
        $creator->createOriginStopover();
        $creator->createDestinationStopover();
        $trip->refresh();

        return redirect()->route('trains.trip', [
            'tripID'    => $trip->trip_id,
            'lineName'  => $trip->linename,
            'start'     => $trip->origin,
            'departure' => $trip->departure->toIso8601String(),
        ]);
    }
}
