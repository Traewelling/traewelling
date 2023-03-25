<?php

namespace App\Listeners;

use App\Enum\WebhookEvent;
use App\Events\StatusDeleteEvent;
use App\Http\Controllers\Backend\WebhookController;

class StatusDeleteWebhookListener
{
    public function handle(StatusDeleteEvent $event) {
        WebhookController::sendStatusWebhook($event->status, WebhookEvent::CHECKIN_DELETE);
    }
}
