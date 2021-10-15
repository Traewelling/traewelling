<?php

namespace App\Http\Controllers\Frontend;

use App\Enum\Business;
use App\Http\Controllers\Backend\StatisticController as StatisticBackend;
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

        $globalStats = StatisticBackend::getGlobalCheckInStats($from, $to);

        $topCategories  = StatisticBackend::getTopTravelCategoryByUser(auth()->user(), $from, $to);
        $topOperators   = StatisticBackend::getTopTripOperatorByUser(auth()->user(), $from, $to);
        $travelTime     = StatisticBackend::getWeeklyTravelTimeByUser(auth()->user(), $from, $to);
        $travelPurposes = StatisticBackend::getTravelPurposes(auth()->user(), $from, $to);

        $travelPurposes = $travelPurposes->map(function($row) {
            if ($row->reason === Business::PRIVATE) {
                $row->reason = __('stationboard.business.private');
            } elseif ($row->reason === Business::BUSINESS) {
                $row->reason = __('stationboard.business.business');
            } elseif ($row->reason === Business::COMMUTE) {
                $row->reason = __('stationboard.business.commute');
            }
            return $row;
        });

        $topCategories = $topCategories->map(function($row) {
            $row->name = __('transport_types.' . $row->name);
            return $row;
        });

        return view('stats.stats', [
            'from'           => $from,
            'to'             => $to,
            'globalStats'    => $globalStats,
            'topCategories'  => $topCategories,
            'topOperators'   => $topOperators,
            'travelTime'     => $travelTime,
            'travelPurposes' => $travelPurposes,
        ]);
    }
}
