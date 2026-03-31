<?php

namespace App\Enum;

use App\Helpers\Lang;

enum EventRejectionReason: string
{
    case DEFAULT = 'denied';
    case LATE = 'too-late';
    case DUPLICATE = 'duplicate';
    case NOT_APPLICABLE = 'not-applicable';
    case MISSING_INFORMATION = 'missing-information';

    public function getReason(?string $locale = null): string
    {
        return Lang::trans(sprintf('notifications.eventSuggestionProcessed.%s', $this->value), [], $locale);
    }

    public function getXPChange(): int
    {
        return match ($this) {
            self::DUPLICATE, self::LATE, self::DEFAULT => 0,
            self::NOT_APPLICABLE => -1,
            self::MISSING_INFORMATION => -5,
        };
    }
}
