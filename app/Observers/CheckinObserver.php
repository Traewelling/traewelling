<?php

namespace App\Observers;

use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\Checkin;

class CheckinObserver
{
    public function updated(Checkin $checkin): void {
        if ($checkin->isDirty(['origin', 'destination', 'departure', 'arrival', 'manual_departure', 'manual_arrival'])) {
            //if origin, destination, departure or arrival is changed, update duration
            TrainCheckinController::calculateCheckinDuration($checkin->fresh());
        }
    }
}
