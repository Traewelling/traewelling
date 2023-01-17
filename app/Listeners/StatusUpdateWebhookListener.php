<?php

namespace App\Listeners;

use App\Enum\WebhookEventEnum;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\WebhookController;

class StatusUpdateWebhookListener
{
    public function handle(StatusUpdateEvent $event) {
        WebhookController::sendStatusWebhook($event->status, WebhookEventEnum::CHECKIN_UPDATE);
    }
}
