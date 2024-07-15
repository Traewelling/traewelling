<?php declare(strict_types=1);

namespace App\Notifications;

class ErrorTextNotification extends TextNotification
{
    public static function getLead(array $data): string {
        return 'Error'; //TODO: localize
    }
}
