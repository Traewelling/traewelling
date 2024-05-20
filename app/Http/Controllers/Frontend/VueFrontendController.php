<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class VueFrontendController
{
    public function stationboard(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application {
        return view('vuestationboard', []);
    }
}
