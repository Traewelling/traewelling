<?php

namespace App\DataProviders\Repositories;

use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class TripRepository
{
    /**
     * @param Trip                 $trip
     * @param Collection<Stopover> $stopovers
     *
     * @return void
     */
    public function tryToSaveStopovers(Trip $trip, Collection $stopovers): void {
        foreach ($stopovers as $stopover) {
            try {
                $trip->stopovers()->save($stopover);
            } catch (Throwable $e) {
                Log::critical(
                    'Failed creating Stopover: ' . $e->getMessage(),
                    [
                        'trip'          => $trip->id,
                        'stopover'      => $stopover->id,
                        'station'       => $stopover->station->id,
                        'departure'     => $stopover->departure_planned,
                        'departure_utc' => (clone $stopover->departure_planned)->tz('UTC'),
                        'arrival'       => $stopover->arrival_planned,
                        'arrival_utc'   => (clone $stopover->arrival_planned)->tz('UTC'),
                    ]
                );
            }
        }
    }
}
