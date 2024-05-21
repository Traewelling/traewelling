<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\HafasTravelType;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Resources\TripResource;
use App\Models\HafasOperator;
use App\Models\Trip;
use App\Models\Station;
use App\Models\Stopover;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class TripController extends Controller
{

    /**
     * Undocumented beta endpoint - only specific users have access
     *
     * @param Request $request
     *
     * @return TripResource
     *
     * @todo add docs
     * @todo currently the stations need to be in the database. We need to add a fallback to HAFAS.
     *       -> later solve the problem for non-existing stations
     */
    public function createTrip(Request $request): TripResource {
        if (!auth()->user()?->can('create-manual-trip')) {
            abort(403, 'this endpoint is currently only available for beta users');
        }

        $validated = $request->validate(
            [
                'category'                  => ['required', new Enum(HafasTravelType::class)],
                'lineName'                  => ['required'],
                'journeyNumber'             => ['nullable', 'numeric', 'min:1'],
                'operatorId'                => ['nullable', 'numeric', 'exists:hafas_operators,id'],
                'originId'                  => ['required', 'exists:train_stations,id'],
                'originDeparturePlanned'    => ['required', 'date'],
                'originDepartureReal'       => ['nullable', 'date'],
                'destinationId'             => ['required', 'exists:train_stations,id'],
                'destinationArrivalPlanned' => ['required', 'date'],
                'destinationArrivalReal'    => ['nullable', 'date'],
                'stopovers.*.stationId'     => ['required', 'exists:train_stations,id'],
                'stopovers.*.arrival'       => ['required_without:stopovers.*.departure', 'required_with:stopovers.*.arrivalReal', 'date'],
                'stopovers.*.arrivalReal'   => ['nullable', 'date'],
                'stopovers.*.departure'     => ['required_without:stopovers.*.arrival,null', 'required_with:stopovers.*.departureReal', 'date'],
                'stopovers.*.departureReal' => ['nullable', 'date'],
            ]
        );

        DB::beginTransaction();

        $creator = new ManualTripCreator();
        $creator->setCategory(HafasTravelType::from($validated['category']))
                ->setLine($validated['lineName'], $validated['journeyNumber'])
                ->setOperator(HafasOperator::find($validated['operatorId']))
                ->setOrigin(
                    Station::findOrFail($validated['originId']),
                    Carbon::parse($validated['originDeparturePlanned']),
                    isset($validated['originDepartureReal']) ? Carbon::parse($validated['originDepartureReal']) : null
                )
                ->setDestination(
                    Station::findOrFail($validated['destinationId']),
                    Carbon::parse($validated['destinationArrivalPlanned']),
                    isset($validated['destinationArrivalReal']) ? Carbon::parse($validated['destinationArrivalReal']) : null
                );

        if (isset($validated['stopovers'])) {
            foreach ($validated['stopovers'] as $stopover) {
                $creator->addStopover(
                    Station::findOrFail($stopover['stationId']),
                    isset($stopover['departure']) ? Carbon::parse($stopover['departure']) : null,
                    isset($stopover['arrival']) ? Carbon::parse($stopover['arrival']) : null,
                    isset($stopover['departureReal']) ? Carbon::parse($stopover['departureReal']) : null,
                    isset($stopover['arrivalReal']) ? Carbon::parse($stopover['arrivalReal']) : null
                );
            }
        }

        $trip = $creator->createFullTrip();

        DB::commit();

        return new TripResource($trip);
    }
}
