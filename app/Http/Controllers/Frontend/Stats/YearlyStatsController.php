<?php

namespace App\Http\Controllers\Frontend\Stats;

use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use App\Http\Controllers\Backend\Stats\YearlyStatsController as YearlyStatsBackend;
use Illuminate\View\View;

class YearlyStatsController extends Controller
{
    public function renderYearlyStats(string $year): View {
        $statuses = YearlyStatsBackend::getStatusesOnDate(Auth::user(), (int)$year)
                                     ->map(function(Status $status) {
                                         $status->mapLines = GeoController::getMapLinesForCheckin($status->trainCheckin, true);
                                         return $status;
                                     });

        $yearBefore = ((int)$year) - 1;
        $yearAfter = null;
        if((int) $year < (new Carbon())->year) {
            $yearAfter = ((int)$year) + 1;
        }

        return view('stats.yearly', [
            'year'     => $year,
            'yearBefore' => $yearBefore,
            'yearAfter' => $yearAfter,
            'statuses' => $statuses,
        ]);
    }
}
