<?php

namespace App\Http\Controllers\Frontend;



use Illuminate\View\View;

class VueFrontendController
{
    public function stationboard(): View {
        return view('vuestationboard', []);
    }
}
