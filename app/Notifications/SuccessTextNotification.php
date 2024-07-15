<?php declare(strict_types=1);

namespace App\Notifications;

class SuccessTextNotification extends TextNotification
{
    public static function getLead(array $data): string {
        return 'Yey'; //TODO: localize
    }
}
