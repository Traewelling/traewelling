<?php

namespace App\Enum;

enum EventRejectionReason: string
{
    case DEFAULT = 'denied';
    case LATE = 'too-late';
    case DUPLICATE = 'duplicate';
    case NOT_APPLICABLE = 'not-applicable';

    public function getReason(): string {
        return sprintf('notifications.eventSuggestionProcessed.%s', $this->value);
    }
}
