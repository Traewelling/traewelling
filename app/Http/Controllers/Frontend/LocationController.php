<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\View\View;

class LocationController extends Controller
{

    public function renderLocation(string $slug): View {
        $location = Location::where('slug', $slug)->firstOrFail();

        return view('location', [
            'location' => $location,
            'checkins' => $location->checkins()->paginate(10),
        ]);
    }
}
