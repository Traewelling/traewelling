<?php

namespace App\Listeners;

use App\Enum\WebhookEvent;
use App\Events\UserCheckedIn;
use App\Http\Controllers\Backend\BrouterController;
use App\Http\Controllers\Backend\WebhookController;

class StatusCreateCheckPolylineListener
{
    public function handle(UserCheckedIn $event): void {
        BrouterController::checkPolyline($event->status->checkin->HafasTrip);
    }
}
