<?php declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;

abstract class TextNotification extends Notification implements BaseNotification
{

    public static function getNotice(array $data): ?string {
        if (!isset($data['locale'])) { //should not happen, but just in case
            return $data['textEn'];
        }
        return str_starts_with($data['locale'], 'de') ? $data['textDe'] : $data['textEn'];
    }

    public static function getLink(array $data): ?string {
        return null;
    }

}
