<?php

declare(strict_types=1);

namespace App\Enum;

enum WebhookEvent: int {
    case CHECKIN_CREATE = 1 << 0;
    case CHECKIN_UPDATE = 1 << 1;
    case CHECKIN_DELETE = 1 << 2;
    case NOTIFICATION = 1 << 3;

    public function name(): string {
        return match ($this) {
            WebhookEvent::CHECKIN_CREATE => 'checkin_create',
            WebhookEvent::CHECKIN_UPDATE => 'checkin_update',
            WebhookEvent::CHECKIN_DELETE => 'checkin_delete',
            WebhookEvent::NOTIFICATION => 'notification',
        };
    }

    public static function fromNames(array $names): int {
        return array_reduce(array_filter(WebhookEvent::cases(), function ($event) use ($names) {
            return in_array($event->name(), $names);
        }), function ($acc, $value) {
            return $acc | $value->value;
        }, 0);
    }
}
