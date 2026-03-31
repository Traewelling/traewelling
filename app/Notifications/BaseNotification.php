<?php

namespace App\Notifications;

interface BaseNotification
{
    public static function getLead(array $data, ?string $locale = null): string;

    public static function getNotice(array $data, ?string $locale = null): ?string;

    /**
     * @return string|null optionally link to which the user should be redirected if clicked on the notification
     */
    public static function getLink(array $data): ?string;

    public function toArray(): array;
}
