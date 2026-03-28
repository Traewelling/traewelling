<?php

namespace App\Observers;

use App\Models\Checkin;
use App\Services\Checkin\CheckinService;

class CheckinObserver
{
    public function __construct(private readonly CheckinService $checkinService) {}

    public function updated(Checkin $checkin): void
    {
        if ($checkin->isDirty(['origin', 'destination', 'departure', 'arrival', 'manual_departure', 'manual_arrival'])) {
            // if origin, destination, departure or arrival is changed, update duration
            $this->checkinService->calculateCheckinDuration($checkin->fresh());
        }
    }
}
