<?php declare(strict_types=1);

namespace App\Notifications;

class WarningTextNotification extends TextNotification
{
    public static function getLead(array $data): string {
        return 'Warning'; //TODO: localize
    }
}
