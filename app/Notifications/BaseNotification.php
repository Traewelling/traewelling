<?php

namespace App\Notifications;

interface BaseNotification
{

    public static function getLead(array $data): string;

    public static function getNotice(array $data): ?string;

    /**
     * @return string|null optionally link to which the user should be redirected if clicked on the notification
     */
    public static function getLink(array $data): ?string;
}
