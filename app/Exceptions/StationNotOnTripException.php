<?php

namespace App\Exceptions;

use App\Models\HafasTrip;
use App\Models\TrainStation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StationNotOnTripException extends Referencable
{

    /**
     * @param TrainStation $origin
     * @param TrainStation $destination
     * @param Carbon       $departure
     * @param Carbon       $arrival
     * @param HafasTrip    $trip
     */
    public function __construct(
        ?TrainStation $origin = null,
        ?TrainStation $destination = null,
        ?Carbon $departure = null,
        ?Carbon $arrival = null,
        ?HafasTrip $trip = null
    ) {
        $this->context = [
            'origin'      => $origin->id ?? null,
            'destination' => $destination->id ?? null,
            'departure'   => $departure ? $departure->toIso8601String() : null,
            'arrival'     => $arrival ? $arrival->toIso8601String() : null,
            'trip'        => $trip->trip_id ?? null
        ];

        parent::__construct("Station not on trip");

        Log::debug('Checkin: No stop found for origin or destination (HafasTrip ' . $this->context['trip'] . ')');
        Log::debug('Checkin: Origin: ' . $this->context['origin'] . ', ' . $this->context['departure']);
        Log::debug('Checkin: Destination: ' . $this->context['destination'] . ', ' . $this->context['arrival']);
    }
}
