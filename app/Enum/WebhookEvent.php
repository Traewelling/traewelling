<?php

declare(strict_types=1);

namespace App\Enum;

// Note: Webhook Event strings must not be longer than 32 characters.
enum WebhookEvent: string
{
    case CHECKIN_CREATE = "checkin_create";
    case CHECKIN_UPDATE = "checkin_update";
    case CHECKIN_DELETE = "checkin_delete";
    case NOTIFICATION   = "notification";
}
