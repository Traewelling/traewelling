<?php

namespace App\Http\Controllers\Frontend\Transport;

use App\Http\Controllers\Controller;
use App\Models\TrainCheckin;
use App\Models\TrainStopover;
use Illuminate\Http\Request;
use App\Http\Controllers\Backend\Transport\TrainCheckinController as TrainCheckinBackend;

class TrainCheckinController extends Controller
{
    public function changeDestination(Request $request) {
        $validated = $request->validate([
                                            'checkin_id'  => ['required', 'exists:train_checkins,id'],
                                            'stopover_id' => ['required', 'exists:train_stopovers,id'],
                                        ]);

        $checkin = TrainCheckin::findOrFail($validated['checkin_id']);
        if ($checkin->user_id !== auth()->id()) {
            return back()->with('alert-danger', __('controller.status.not-permitted'));
        }
        $newDestination = TrainStopover::findOrFail($validated['stopover_id']);

        //TODO: Checks

        $pointReason = TrainCheckinBackend::changeDestination($checkin, $newDestination);

        return redirect()->route('dashboard')->with('checkin-success', [
            'distance'                => $checkin->distance,
            'duration'                => $checkin->duration,
            'points'                  => $checkin->points,
            'lineName'                => $checkin->HafasTrip->linename,
            'alsoOnThisConnection'    => $checkin->alsoOnThisConnection,
            'event'                   => $checkin->event,
            'forced'                  => isset($validated['force']),
            'pointsCalculationReason' => $pointReason,
        ]);
    }
}
