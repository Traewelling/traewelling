<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\StatisticController as StatisticBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticController extends Controller
{
    public function renderStations(Request $request): View
    {
        if (!auth()->check() || !auth()->user()->hasRole('closed-beta')) {
            abort(404);
        }

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subQuarter()->startOfDay();
        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : Carbon::now()->endOfDay();

        $usedStations = StatisticBackend::getUsedStations(auth()->user(), $from, $to);
        $passedStations = StatisticBackend::getPassedStations(auth()->user(), $from, $to)
            ->reject(function ($station) use ($usedStations) {
                return $usedStations->contains('id', $station->id);
            });

        return view('stats.stations', [
            'usedStations' => $usedStations,
            'passedStations' => $passedStations,
        ]);

    }
}
