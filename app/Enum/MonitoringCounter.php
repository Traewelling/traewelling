<?php

declare(strict_types=1);

namespace App\Enum;

enum MonitoringCounter: string {
    case StatusCreated = "StatusCreated";
    case StatusDeleted = "StatusDeleted";
    case UserCreated   = "UserCreated";
    case UserDeleted   = "UserDeleted";
    case WebhookAbsent = "WebhookAbsent";
}
