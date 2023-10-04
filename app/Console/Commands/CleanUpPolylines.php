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
        $usedParentIds   = Polyline::where('parent_id', '<>', null)->groupBy('parent_id')->select('parent_id');
        $affectedRows    = Polyline::whereNotIn('id', $usedPolylineIds)->whereNotIn('id', $usedParentIds)->delete();
        Log::debug($affectedRows . ' unused polylines deleted.');
        return 0;
    }
}
