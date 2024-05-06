<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Models\Trip;
use Illuminate\View\View;

class TripController
{

    public function renderTrip(string $id): View {
        $trip = Trip::with(['checkins', 'polyline.parent'])
                    ->where('trip_id', $id)
                    ->firstOrFail();
        return view('admin.trip.show', [
            'trip' => $trip
        ]);
    }
}
