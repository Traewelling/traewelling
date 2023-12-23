<?php

namespace App\Console\Commands;

use App\Models\HafasTrip;
use App\Models\Checkin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanUpHafasTrips extends Command
{
    protected $signature   = 'trwl:cleanUpHafasTrips';
    protected $description = 'Delete unused and old HafasTrips from database';

    public function handle(): int {
        $usedTripIds  = Checkin::groupBy('trip_id')->select('trip_id');
        $affectedRows = HafasTrip::whereNotIn('trip_id', $usedTripIds)->delete();
        Log::debug($affectedRows . ' unused HafasTrips deleted.');
        return 0;
    }
}
