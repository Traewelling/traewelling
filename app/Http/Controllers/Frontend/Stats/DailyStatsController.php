<?php

namespace App\Http\Controllers\Frontend\Stats;

use App\Enum\Business;
use App\Enum\CacheKey;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Backend\StatisticController as StatisticBackend;
use App\Http\Controllers\Controller;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

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
