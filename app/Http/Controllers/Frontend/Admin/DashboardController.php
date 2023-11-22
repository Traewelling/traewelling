<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Controllers\Backend\Admin\DashboardController as DashboardBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function renderStats(Request $request): View {
        $validated = $request->validate([
                                            'since' => ['nullable', 'date'],
                                            'until' => ['nullable', 'date'],
                                        ]);

        $since = isset($validated['since']) ? Carbon::parse($validated['since']) : Carbon::today()->subDays(14);
        $until = isset($validated['until']) ? Carbon::parse($validated['until']) : Carbon::today()->endOfDay();

        return view('admin.stats', [
            'since'                   => $since,
            'until'                   => $until,
            'statusesByDate'          => DashboardBackend::getStatusesByDate($since, $until),
            'registrationsByDate'     => DashboardBackend::getRegistrationsByDate($since, $until),
            'hafasAndPolylinesByDate' => DashboardBackend::getHafasAndPolylinesByDate($since, $until),
        ]);
    }
}
