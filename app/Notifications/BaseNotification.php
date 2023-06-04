<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{

    /**
     * @return string the class names in font awesome
     */
    public static abstract function getIcon(): string;

    public static abstract function getLead(array $data): string;

    public static abstract function getNotice(array $data): ?string;

    /**
     * @return string|null optionally link to which the user should be redirected if clicked on the notification
     */
    public static abstract function getLink(array $data): ?string;
}
