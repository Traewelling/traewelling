<?php


declare(strict_types=1);

namespace App\Enum;

enum WebhookEventEnum: string
{
    case CHECKIN_CREATE = 'checkin_create';
    case CHECKIN_UPDATE = 'checkin_update';
    case CHECKIN_DELTE = 'checkin_delete';
    case WEBHOOK_CREATED = 'webhook_created';
    case WEBHOOK_DELETED = 'webhook_deleted';
}
