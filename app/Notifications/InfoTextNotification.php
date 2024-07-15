<?php declare(strict_types=1);

namespace App\Notifications;

class InfoTextNotification extends TextNotification
{
    public static function getLead(array $data): string {
        return 'Info'; //TODO: localize
    }
}
