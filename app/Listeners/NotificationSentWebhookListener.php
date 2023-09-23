<?php

namespace App\Listeners;

use App\Http\Controllers\Backend\WebhookController;
use App\Models\User;
use Illuminate\Notifications\Events\NotificationSent;

class NotificationSentWebhookListener
{
    public function handle(NotificationSent $event)
    {
        if ($event->channel !== 'database') {
            return;
        }
        $user = User::where('id', $event->notifiable->id)->first();
        $notification = $user->notifications->where('id', $event->notification->id)->first();
        WebhookController::sendNotificationWebhook($user, $notification);
    }
}
