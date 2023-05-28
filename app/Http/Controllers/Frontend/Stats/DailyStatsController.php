<?php

namespace App\Http\Controllers\Frontend\Stats;

use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class DailyStatsController extends Controller
{
    public function renderDailyStats(string $dateString) {
        $date     = Date::parse($dateString);
        $statuses = Status::with(['trainCheckin'])
                          ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                          ->where('statuses.user_id', Auth::user()->id)
                          ->where('train_checkins.departure', '>=', $date->clone()->startOfDay()->toIso8601String())
                          ->where('train_checkins.departure', '<=', $date->clone()->endOfDay()->toIso8601String())
                          ->select('statuses.*')
                          ->get()
                          ->sortBy('trainCheckin.departure')
                          ->map(function(Status $status) {
                              $status->mapLines = GeoController::getMapLinesForCheckin($status->trainCheckin, true);
                              return $status;
                          });

        return view('stats.daily', [
            'date'     => $date,
            'statuses' => $statuses,
        ]);
    }
}
