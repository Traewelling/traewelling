<?php

namespace App\Console\Commands;

use App\Models\HafasTrip;
use App\Models\PolyLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanUpPolylines extends Command
{
    protected $signature   = 'trwl:cleanUpPolylines';
    protected $description = 'Delete unused and old polylines from database';

    public function handle(): int {
        $usedPolylineIds = HafasTrip::where('polyline_id', '<>', null)->groupBy('polyline_id')->select('polyline_id');
        $affectedRows    = Polyline::whereNotIn('id', $usedPolylineIds)->delete();
        Log::debug($affectedRows . ' unused polylines deleted.');
        return 0;
    }
}
