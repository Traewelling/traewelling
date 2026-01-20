<?php

namespace App\DataProviders\Repositories;

use App\Enum\TripSource;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class TripRepository
{
    /**
     * @param  Collection<Stopover>  $stopovers
     */
    public function tryToSaveStopovers(Trip $trip, Collection $stopovers): void
    {
        foreach ($stopovers as $stopover) {
            try {
                $trip->stopovers()->updateOrCreate(
                    [
                        'trip_id' => $trip->trip_id,
                        'train_station_id' => $stopover->station->id,
                        'arrival_planned' => $stopover->arrival_planned,
                        'departure_planned' => $stopover->departure_planned,
                    ],
                    [
                        'arrival_real' => $stopover->arrival_real,
                        'departure_real' => $stopover->departure_real,
                        'arrival_platform_planned' => $stopover->arrival_platform_planned,
                        'departure_platform_planned' => $stopover->departure_platform_planned,
                        'arrival_platform_real' => $stopover->arrival_platform_real,
                        'departure_platform_real' => $stopover->departure_platform_real,
                        'station_identifier_id' => $stopover->station_identifier_id,
                    ]
                );
            } catch (Throwable $e) {
                Log::critical(
                    'Failed creating Stopover: ' . $e->getMessage(),
                    [
                        'trip' => $trip->id,
                        'stopover' => $stopover->id,
                        'station' => $stopover->station->id,
                        'departure' => $stopover->departure_planned,
                        'departure_utc' => (clone $stopover->departure_planned)->tz('UTC'),
                        'arrival' => $stopover->arrival_planned,
                        'arrival_utc' => (clone $stopover->arrival_planned)->tz('UTC'),
                    ]
                );
                report($e);
            }
        }
    }

    public function getCurrentActiveTrips(TripSource $source): Collection
    {
        // To only refresh checked in trips join train_checkins:
        return Trip::join('train_checkins', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->join('train_stopovers as origin_stopover', 'origin_stopover.id', '=', 'train_checkins.origin_stopover_id')
            ->join('train_stopovers as destionation_stopover', 'destionation_stopover.id', '=', 'train_checkins.destination_stopover_id')
            ->where(function ($query) {
                $query->where('destionation_stopover.arrival_planned', '>=', now()->subMinutes(20))
                    ->orWhere('destionation_stopover.arrival_real', '>=', now()->subMinutes(20));
            })
            ->where(function ($query) {
                $query->where('origin_stopover.departure_planned', '<=', now()->addMinutes(20))
                    ->orWhere('origin_stopover.departure_real', '<=', now()->addMinutes(20));
            })
            ->where(function ($query) {
                $query->where('hafas_trips.last_refreshed', '<', now()->subMinutes(5))
                    ->orWhereNull('hafas_trips.last_refreshed');
            })
            ->where('hafas_trips.source', $source->value)
            ->select('hafas_trips.*')
            ->distinct()
            ->orderBy('hafas_trips.last_refreshed')
            ->get();
    }
}
