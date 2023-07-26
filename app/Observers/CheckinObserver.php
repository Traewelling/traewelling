<?php

namespace App\Observers;

use App\Models\TrainCheckin;

class CheckinObserver
{

    public function updated(TrainCheckin $checkin): void {
        if ($checkin->isDirty(['origin', 'destination', 'departure', 'arrival'])) {
            //if origin, destination, departure or arrival is changed: set duration to null (will be calculated on next access)
            //Don't use ->update() here, because this will trigger an infinite loop
            TrainCheckin::where('id', $checkin->id)->update(['duration' => null]);
        }
    }
}
