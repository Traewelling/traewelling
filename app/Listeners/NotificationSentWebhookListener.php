<?php

namespace App\Listeners;

use App\Http\Controllers\Backend\WebhookController;
use Illuminate\Notifications\Events\NotificationSent;

class NotificationSentWebhookListener
{
    public function handle(NotificationSent $event)
    {
        if ($event->channel !== 'database') {
            return;
        }
        WebhookController::sendNotificationWebhook($event->notifiable, $event->response);
    }
}
