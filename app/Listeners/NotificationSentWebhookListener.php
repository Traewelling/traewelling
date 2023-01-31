<?php

namespace App\Listeners;

use App\Http\Controllers\Backend\WebhookController;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

class NotificationSentWebhookListener
{
    public function handle(NotificationSent $event)
    {
        $user = User::where('id', $event->notifiable->id)->first();
        $notification = $user->notifications->find($event->notification->id)->first();
        WebhookController::sendNotificationWebhook($user, $notification);
    }
}
