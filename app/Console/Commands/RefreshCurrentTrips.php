<?php

namespace App\Console\Commands;

use App\Http\Controllers\HafasController;
use App\Models\HafasTrip;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class RefreshCurrentTrips extends Command
{
    protected $signature   = 'trwl:refreshTrips';
    protected $description = 'Refresh delay data from current active trips';

    public function handle(): int {
        $activeTripIds = TrainStopover::where('arrival_planned', '<=', Carbon::now()->addHours(2)->toIso8601String())
                                      ->where(function($query) {
                                          $query->where('arrival_planned', '>=', Carbon::now()->toIso8601String())
                                                ->orWhere('arrival_real', '>=', Carbon::now()->toIso8601String());
                                      })
                                      ->groupBy('trip_id')
                                      ->select('trip_id')
                                      ->get()
                                      ->pluck('trip_id');

        //Join is to filter to only checked in journeys
        $trips = HafasTrip::join('train_checkins', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
                          ->select('hafas_trips.*')
                          ->whereIn('hafas_trips.trip_id', $activeTripIds)
                          ->where('hafas_trips.arrival', '<=', Carbon::now()->addDay()->toIso8601String())
                          ->get();

        if ($trips->count() === 0) {
            $this->info('There are currently no trips to refresh.');
            return 0;
        }

        foreach ($trips as $trip) {
            try {
                $this->info("Refreshing " . $trip->linename . "...");
                HafasController::fetchHafasTrip($trip->trip_id, $trip->linename);
            } catch (Throwable $exception) {
                report($exception);
                echo "Error while refreshing " . $trip->linename . "! " . $exception->getMessage() . "\r\n";
                echo "Full error is available in the server log.\r\n";
            }
        }

        return 0;
    }
}
