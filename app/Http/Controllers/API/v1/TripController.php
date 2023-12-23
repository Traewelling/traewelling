<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\HafasTravelType;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Resources\HafasTripResource;
use App\Models\HafasOperator;
use App\Models\HafasTrip;
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
     * @return HafasTripResource
     *
     * @todo add stopovers
     * @todo add docs
     * @todo currently the stations need to be in the database. We need to add a fallback to HAFAS.
     *       -> later solve the problem for non-existing stations
     */
    public function createTrip(Request $request): HafasTripResource {
        if (!auth()->user()?->hasRole('closed-beta')) {
            abort(403, 'this endpoint is currently only available for beta users');
        }

        $validated = $request->validate([
                                            'category'                  => ['required', new Enum(HafasTravelType::class)],
                                            'lineName'                  => ['required'],
                                            'journeyNumber'             => ['nullable', 'numeric', 'min:1'],
                                            'operatorId'                => ['nullable', 'numeric', 'exists:hafas_operators,id'],
                                            'originId'                  => ['required', 'exists:train_stations,ibnr'],
                                            'originDeparturePlanned'    => ['required', 'date'],
                                            'destinationId'             => ['required', 'exists:train_stations,ibnr'],
                                            'destinationArrivalPlanned' => ['required', 'date'],
                                        ]);

        DB::beginTransaction();

        $creator = new ManualTripCreator();

        $creator->category                  = HafasTravelType::from($validated['category']);
        $creator->lineName                  = $validated['lineName'];
        $creator->journeyNumber             = $validated['journeyNumber'];
        $creator->operator                  = HafasOperator::find($validated['operatorId']);
        $creator->origin                    = Station::where('ibnr', $validated['originId'])->firstOrFail();
        $creator->originDeparturePlanned    = Carbon::parse($validated['originDeparturePlanned']);
        $creator->destination               = Station::where('ibnr', $validated['destinationId'])->firstOrFail();
        $creator->destinationArrivalPlanned = Carbon::parse($validated['destinationArrivalPlanned']);

        $trip = $creator->createTrip();
        $creator->createOriginStopover();
        $creator->createDestinationStopover();

        DB::commit();

        return new HafasTripResource($trip);
    }
}
