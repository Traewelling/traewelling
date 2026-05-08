<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Models\Trip;
use Illuminate\View\View;

class TripController
{
    public function index(): View
    {
        $trips = Trip::withCount('checkins')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.trips.index', [
            'trips' => $trips,
        ]);
    }
}
