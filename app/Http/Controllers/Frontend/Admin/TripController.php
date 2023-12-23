<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Enum\HafasTravelType;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Controllers\Backend\Transport\ManualTripCreator as TripBackend;
use App\Models\HafasOperator;
use App\Models\HafasTrip;
use App\Models\Station;
use App\Models\Stopover;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class TripController
{

    public function renderTrip(string $id): View {
        $trip = HafasTrip::with(['checkins'])
                         ->where('trip_id', $id)
                         ->firstOrFail();
        return view('admin.trip.show', [
            'trip' => $trip
        ]);
    }
}
