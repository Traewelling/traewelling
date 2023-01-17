<?php

namespace App\Listeners;

use App\Enum\WebhookEventEnum;
use App\Events\UserCheckedIn;
use App\Http\Controllers\Backend\WebhookController;

class StatusCreateWebhookListener
{
    public function handle(UserCheckedIn $event) {
        WebhookController::sendStatusWebhook($event->status, WebhookEventEnum::CHECKIN_CREATE);
    }
}
