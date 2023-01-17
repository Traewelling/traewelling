<?php

namespace App\Listeners;

use App\Enum\WebhookEventEnum;
use App\Events\StatusDeleteEvent;
use App\Http\Controllers\Backend\WebhookController;

class StatusDeleteWebhookListener
{
    public function handle(StatusDeleteEvent $event) {
        WebhookController::sendStatusWebhook($event->status, WebhookEventEnum::CHECKIN_DELETE);
    }
}
