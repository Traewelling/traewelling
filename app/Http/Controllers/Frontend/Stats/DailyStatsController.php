<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend\Stats;

use App\Http\Controllers\Backend\Stats\DailyStatsController as DailyStatsBackend;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

class DailyStatsController extends Controller
{
    public function renderDailyStats(string $dateString): View
    {
        $date = Date::parse($dateString, Auth::user()->timezone);

        return view('stats.daily', [
            'date' => $date,
            'prevDate' => DailyStatsBackend::getPrevDateWithStatuses(Auth::user(), $date),
            'nextDate' => DailyStatsBackend::getNextDateWithStatuses(Auth::user(), $date),
        ]);
    }
}
