<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\MotisSourceLicense;
use Illuminate\View\View;

/**
 * Frontend-Controller for some debug views - so user can see which data is used by current träwelling instance.
 */
class DebugController extends Controller
{
    public function showMotisSources(): View
    {
        return view('debug.motis-source', [
            'sources' => MotisSourceLicense::with('manualLicense')->orderBy('country')->get(),
        ]);
    }

    public function showStationMap(): View
    {
        return view('temp-vue-inclusion', [
            'pageTitle' => 'Station Map Debug',
            'vueComponent' => 'station-map',
        ]);
    }
}
