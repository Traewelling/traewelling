<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\Checkin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanUpHafasTrips extends Command
{
    protected $signature   = 'trwl:cleanUpHafasTrips';
    protected $description = 'Delete unused and old Trips from database';

    public function handle(): int {
        $usedTripIds  = Checkin::groupBy('trip_id')->select('trip_id');
        $affectedRows = Trip::whereNotIn('trip_id', $usedTripIds)->delete();
        Log::debug($affectedRows . ' unused Trips deleted.');
        return 0;
    }
}
