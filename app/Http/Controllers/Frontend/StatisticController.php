<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;

class StatisticController extends Controller
{
    public function renderMainStats(): Renderable {
        return view('stats.stats');
    }
}
