<?php

namespace App\Listeners;

use App\Enum\WebhookEvent;
use App\Events\UserCheckedIn;
use App\Http\Controllers\Backend\WebhookController;

class StatusCreateWebhookListener {
    public function handle(UserCheckedIn $event) {
        WebhookController::sendStatusWebhook($event->status, WebhookEvent::CHECKIN_CREATE);
    }
}
