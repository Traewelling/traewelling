<?php

namespace App\Console\Commands;

use App\Http\Controllers\HafasController;
use App\Models\HafasTrip;
use App\Models\TrainStopOver;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RefreshCurrentTrips extends Command
{
    protected $signature = 'trwl:refreshTrips';

    protected $description = 'Refresh delay data from current active trips';

    public function handle(): int {

        $qStops = TrainStopOver::where('arrival_planned', '>=', DB::raw('CURRENT_TIMESTAMP'))
                               ->orWhere('arrival_real', '>=', DB::raw('CURRENT_TIMESTAMP'))
                               ->select('trip_id')
                               ->distinct();
        $trips  = HafasTrip::whereIn('trip_id', $qStops)->get();

        if ($trips->count() == 0) {
            echo "There are currently no trips to refresh.\r\n";
            return 0;
        }

        foreach ($trips as $trip) {
            try {
                echo "Refreshing " . $trip->linename . "...\r\n";
                HafasController::fetchHafasTrip($trip->trip_id, $trip->linename);
            } catch (Exception | Throwable $e) {
                report($e);
                echo "Error while refreshing " . $trip->linename . "! " . $e->getMessage() . "\r\n";
                echo "Full error is available in the server log.\r\n";
            }
        }

        return 0;
    }
}