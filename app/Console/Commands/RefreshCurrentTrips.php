<?php

namespace App\Console\Commands;

use App\Enum\TripSource;
use App\Exceptions\HafasException;
use App\Http\Controllers\HafasController;
use App\Models\HafasTrip;
use App\Models\Checkin;
use Illuminate\Console\Command;
use PDOException;

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
                          ->where('hafas_trips.source', TripSource::HAFAS->value)
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
            try {
                $this->info('Refreshing trip ' . $trip->trip_id . ' (' . $trip->linename . ')...');
                $trip->update(['last_refreshed' => now()]);

                $rawHafas    = HafasController::fetchRawHafasTrip($trip->trip_id, $trip->linename);
                $updatedRows = HafasController::refreshStopovers($rawHafas);
                $this->info('Updated ' . $updatedRows . ' rows.');

                //set duration for refreshed trips to null, so it will be recalculated
                Checkin::where('trip_id', $trip->trip_id)->update(['duration' => null]);
            } catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    $this->warn('-> Skipping, due to integrity constraint violation');
                } else {
                    report($exception);
                }
            } catch (HafasException) {
                // Do nothing
            } catch (\Exception $exception) {
                report($exception);
            }

            if ($loop++ >= config('trwl.refresh.max_trips_per_minute')) {
                $this->warn('Max number of trips reached. Waiting for next minute...');
                return 0;
            }
        }
        return 0;
    }
}
