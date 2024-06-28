<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Models\Trip;
use Illuminate\View\View;

class TripController
{

    public function renderTrip(int $id): View {
        $trip = Trip::with(['checkins', 'polyline.parent'])
                    ->findOrFail($id);
        return view('admin.trip.show', [
            'trip' => $trip
        ]);
    }
}
