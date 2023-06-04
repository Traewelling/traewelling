<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{

    abstract public static function getLead(array $data): string;

    abstract public static function getNotice(array $data): ?string;

    /**
     * @return string|null optionally link to which the user should be redirected if clicked on the notification
     */
    abstract public static function getLink(array $data): ?string;
}
