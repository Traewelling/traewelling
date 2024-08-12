<?php

namespace App\Enum;

enum EventRejectionReason: string
{
    case DEFAULT             = 'denied';
    case LATE                = 'too-late';
    case DUPLICATE           = 'duplicate';
    case NOT_APPLICABLE      = 'not-applicable';
    case MISSING_INFORMATION = 'missing-information';

    public function getReason(): string {
        return __(sprintf('notifications.eventSuggestionProcessed.%s', $this->value));
    }
}
