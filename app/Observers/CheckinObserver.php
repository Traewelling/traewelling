<?php

namespace App\Observers;

use App\Models\Checkin;

class CheckinObserver
{

    public function updated(Checkin $checkin): void {
        if ($checkin->isDirty(['origin', 'destination', 'departure', 'arrival'])) {
            //if origin, destination, departure or arrival is changed: set duration to null (will be calculated on next access)
            //Don't use ->update() here, because this will trigger an infinite loop
            Checkin::where('id', $checkin->id)->update(['duration' => null]);
        }
    }
}
