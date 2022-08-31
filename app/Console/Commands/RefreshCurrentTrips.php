<?php

namespace App\Console\Commands;

use App\Http\Controllers\HafasController;
use App\Models\HafasTrip;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RefreshCurrentTrips extends Command
{
    protected $signature   = 'trwl:refreshTrips';
    protected $description = 'Refresh delay data from current active trips';

    public function handle(): int {
        $this->info('Gettings trips to be refreshed...');

        $trips = HafasTrip::join('train_stopovers', 'hafas_trips.trip_id', '=', 'train_stopovers.trip_id')
            //To only refresh checked in trips join train_checkins:
                          ->join('train_checkins', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                          ->where(function($query) {
                              $query->where('train_stopovers.arrival_planned', '>=', now())
                                    ->orWhere('train_stopovers.arrival_real', '>=', now())
                                    ->orWhere('train_stopovers.departure_planned', '>=', now())
                                    ->orWhere('train_stopovers.departure_real', '>=', now());
                          })
                          ->where(function($query) {
                              $query->where('hafas_trips.last_refreshed', '<', now()->subMinutes(5))
                                    ->orWhereNull('hafas_trips.last_refreshed');
                          })
                          ->select('hafas_trips.*')
                          ->distinct()
                          ->orderBy('hafas_trips.last_refreshed')
                          ->get();

        if ($trips->isEmpty()) {
            $this->warn('No trips to be refreshed');
            return 0;
        }

        $this->info('Found ' . $trips->count() . ' trips.');

        $loop = 1;
        foreach ($trips as $trip) {
            $this->info('Refreshing trip ' . $trip->trip_id . ' (' . $trip->linename . ')...');

            $rawHafas = HafasController::fetchRawHafasTrip($trip->trip_id, $trip->linename);

            $payload = [];
            foreach ($rawHafas?->stopovers ?? [] as $stopover) {
                $timestampToCheck = Carbon::parse($stopover->departure ?? $stopover->arrival);
                if ($timestampToCheck->isPast()) {
                    //HAFAS doesn't give as real time information on past stopovers, so... don't overwrite our data. :)
                    continue;
                }

                $stop             = HafasController::parseHafasStopObject($stopover->stop);
                $arrivalPlanned   = Carbon::parse($stopover->plannedArrival);
                $arrivalReal      = Carbon::parse($stopover->arrival);
                $departurePlanned = Carbon::parse($stopover->plannedDeparture);
                $departureReal    = Carbon::parse($stopover->departure);

                $this->info('Updating stopover ' . $stop->name . ' (' . $stop->ibnr . ') delayed +' . ($departureReal->diffInMinutes($departurePlanned)) . '...');

                $payload[] = [
                    'trip_id'           => $rawHafas->id,
                    'train_station_id'  => $stop->id,
                    'arrival_planned'   => $arrivalPlanned->toDateTimeString(),
                    'arrival_real'      => $arrivalReal->toDateTimeString(),
                    'departure_planned' => $departurePlanned->toDateTimeString(),
                    'departure_real'    => $departureReal->toDateTimeString(),
                ];
            }

            $res = TrainStopover::upsert(
                $payload,
                ['trip_id', 'train_station_id', 'departure_planned', 'arrival_planned'],
                ['arrival_real', 'departure_real']
            );

            $this->info('Updated ' . $res . ' rows.');

            $trip->update(['last_refreshed' => now()]);

            if ($loop++ >= config('trwl.refresh.max_trips_per_minute')) {
                $this->warn('Max number of trips reached. Waiting for next minute...');
                return 0;
            }
        }
        return 0;
    }
}
