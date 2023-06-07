<?php

namespace App\Listeners;

use App\Enum\WebhookEvent;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\WebhookController;

class StatusUpdateWebhookListener {
    public function handle(StatusUpdateEvent $event) {
        WebhookController::sendStatusWebhook($event->status, WebhookEvent::CHECKIN_UPDATE);
    }
}
