<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use JetBrains\PhpStorm\ArrayShape;

/**
 * While we have no intentions to remove the twitter integration in the future, it's always good to have a second way to login besides Twitter.
 * With this notification, we ask our users who have a twitter login but no email/password login, to create that second way of authenticating.
 *
 * The notification is the same for everyone who gets it, and does not have custom information.
 */
class TwitterUnstable extends Notification
{
    use Queueable;

    public function __construct() { }

    public static function render($notification) {
        return view("includes.notification", [
            'color'           => "warning",
            'icon'            => "fas fa-exclamation-triangle",
            'lead'            => __('notifications.twitterUnstable.lead'),
            "link"            => route('settings'),
            'notice'          => __('notifications.twitterUnstable.notice'),
            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read'            => $notification->read_at != null,
            'notificationId'  => $notification->id

        ])->render();
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    #[ArrayShape([])]
    public function toArray($notifiable)
    {
        return [];
    }
}
