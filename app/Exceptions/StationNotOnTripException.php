<?php

namespace App\Exceptions;

use App\Models\Station;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StationNotOnTripException extends Referencable
{

    /**
     * @param Station|null $origin
     * @param Station|null $destination
     * @param Carbon|null  $departure
     * @param Carbon|null  $arrival
     * @param Trip|null    $trip
     */
    public function __construct(
        ?Station $origin = null,
        ?Station $destination = null,
        ?Carbon  $departure = null,
        ?Carbon  $arrival = null,
        ?Trip    $trip = null
    ) {
        $this->context = [
            'origin'      => $origin->id ?? null,
            'destination' => $destination->id ?? null,
            'departure'   => $departure?->toIso8601String(),
            'arrival'     => $arrival?->toIso8601String(),
            'trip'        => $trip->trip_id ?? null
        ];

        parent::__construct("Station not on trip");

        Log::debug('Checkin: No stop found for origin or destination (Trip ' . $this->context['trip'] . ')');
        Log::debug('Checkin: Origin: ' . $this->context['origin'] . ', ' . $this->context['departure']);
        Log::debug('Checkin: Destination: ' . $this->context['destination'] . ', ' . $this->context['arrival']);
    }
}
