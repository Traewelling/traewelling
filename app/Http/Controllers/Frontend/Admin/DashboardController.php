<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Controllers\Backend\Admin\DashboardController as DashboardBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function renderDashboard(Request $request): Renderable {
        $since = Carbon::today()->subDays(14);
        $until = Carbon::today()->endOfDay();

        //ToDo: Request...

        return view('admin.dashboard', [
            'since'                   => $since,
            'until'                   => $until,
            'statusesByDate'          => DashboardBackend::getStatusesByDate($since, $until),
            'registrationsByDate'     => DashboardBackend::getRegistrationsByDate($since, $until),
            'transportTypesByDate'    => DashboardBackend::getTransportTypesByDate($since, $until),
            'hafasAndPolylinesByDate' => DashboardBackend::getHafasAndPolylinesByDate($since, $until),
        ]);
    }
}
