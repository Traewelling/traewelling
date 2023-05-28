<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Enum\HafasTravelType;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class TripController
{

    public function renderTrip(string $id): View {
        $trip = HafasTrip::with(['checkIns'])
                         ->where('trip_id', $id)->firstOrFail();
        return view('admin.trip.show', [
            'trip' => $trip
        ]);
    }

    /**
     * This form is currently for testing purposes only.
     * Admins can create a trip with manually entered data.
     * Users can check in to this trip.
     * It should be tested if the trip is created correctly and all data required for the trip is present,
     * so no (500) errors occur.
     *
     * @return View
     */
    public function renderForm(): View {
        return view('admin.trip.create');
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

        $originStation      = TrainStation::where('ibnr', $validated['origin'])->firstOrFail();
        $destinationStation = TrainStation::where('ibnr', $validated['destination'])->firstOrFail();

        $trip = HafasTrip::create([
                                      'trip_id'        => strtr('manual-userId-uuid', [
                                          'userId' => auth()->id(),
                                          'uuid'   => uniqid('', true),
                                      ]),
                                      'category'       => $validated['category'],
                                      'number'         => $validated['number'],
                                      'linename'       => $validated['linename'],
                                      'journey_number' => $validated['journey_number'],
                                      'operator_id'    => $validated['operator_id'],
                                      'origin'         => $validated['origin'],
                                      'destination'    => $validated['destination'],
                                      'departure'      => $validated['departure'],
                                      'arrival'        => $validated['arrival'],
                                  ]);
        //Origin stopover
        TrainStopover::create([
                                  'trip_id'           => $trip->trip_id,
                                  'train_station_id'  => $originStation->id,
                                  'arrival_planned'   => $validated['departure'],
                                  'departure_planned' => $validated['departure'],
                              ]);
        //Destination stopover
        TrainStopover::create([
                                  'trip_id'           => $trip->trip_id,
                                  'train_station_id'  => $destinationStation->id,
                                  'arrival_planned'   => $validated['arrival'],
                                  'departure_planned' => $validated['arrival'],
                              ]);

        return redirect()->route('trains.trip', [
            'tripID'    => $trip->trip_id,
            'lineName'  => $trip->linename,
            'start'     => $trip->origin,
            'departure' => $validated['departure'],
        ]);
    }
}
