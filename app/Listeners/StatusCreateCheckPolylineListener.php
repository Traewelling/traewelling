<?php

namespace App\Listeners;

use App\Events\UserCheckedIn;
use App\Jobs\RefreshPolyline;

class StatusCreateCheckPolylineListener
{
    public function handle(UserCheckedIn $event): void {
        RefreshPolyline::dispatch($event->status->checkin->trip);
    }
}
