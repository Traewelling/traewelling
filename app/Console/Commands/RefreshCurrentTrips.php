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
        $qStops = TrainStopover::where('arrival_planned', '<=', Carbon::now()->addHours(2)->toIso8601String())
                               ->where(function($query) {
                                   $query->where('arrival_planned', '>=', Carbon::now()->toIso8601String())
                                         ->orWhere('arrival_real', '>=', Carbon::now()->toIso8601String());
                               })
                               ->select('trip_id')
                               ->get()
                               ->pluck('trip_id');

        $trips = HafasTrip::whereIn('trip_id', $qStops)
                          ->where('created_at', '>', Carbon::now()->subDays(2)->toIso8601String())
                          ->limit(15) //TODO Roll over all trips instead of refreshing the same 15 every time
                          ->get();

        if ($trips->count() === 0) {
            echo "There are currently no trips to refresh.\r\n";
            return 0;
        }

        foreach ($trips as $trip) {
            try {
                echo "Refreshing " . $trip->linename . "...\r\n";
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
