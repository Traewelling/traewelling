<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Jobs\RefreshPolyline;
use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
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

    public function renderTrip(int $id): View
    {
        $trip = Trip::with(['checkins', 'polyline.parent'])
            ->findOrFail($id);

        return view('admin.trips.show', [
            'trip' => $trip,
        ]);
    }

    public function rerouteTrip(int $id): RedirectResponse
    {
        $trip = Trip::findOrFail($id);

        RefreshPolyline::dispatch($trip);

        return redirect()->route('admin.trips.show', ['id' => $trip->id])
            ->with('status', 'Rerouting job dispatched for trip ID ' . $trip->id);
    }
}
