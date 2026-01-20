<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VueFrontendController
{
    public function stationBoard(Request $request): View
    {
        return view('beta.vue-stationboard', [
            'station' => Station::find((int) $request->stationId),
        ]);
    }

    public function statsDashboard(): View
    {
        return view('stats.stats');
    }
}
