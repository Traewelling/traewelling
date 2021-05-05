<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function renderMainStats(Request $request): Renderable {

        $validated = $request->validate([
                                            'from' => ['nullable', 'date'],
                                            'to'   => ['nullable', 'date', 'after_or_equal:from']
                                        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subWeeks(4);
        $to   = isset($validated['to']) ? Carbon::parse($validated['to']) : Carbon::now();

        $globalStats = \App\Http\Controllers\Backend\StatisticController::getGlobalCheckInStats();

        $topCategories = \App\Http\Controllers\Backend\StatisticController::getTopTravelCategoryByUser(
            auth()->user(), $from, $to
        );
        $topOperators  = \App\Http\Controllers\Backend\StatisticController::getTopTripOperatorByUser(
            auth()->user(), $from, $to
        );
        $travelTime    = \App\Http\Controllers\Backend\StatisticController::getWeeklyTravelTimeByUser(
            auth()->user(), $from, $to
        );

        return view('stats.stats', [
            'from'          => $from,
            'to'            => $to,
            'globalStats'   => $globalStats,
            'topCategories' => $topCategories,
            'topOperators'  => $topOperators,
            'travelTime'    => $travelTime,
        ]);
    }
}
