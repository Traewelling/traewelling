<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Repositories\ReportRepository;
use Illuminate\Support\Facades\Log;

abstract class ReportService
{
    private const array TRIGGER_WORDS = ['auto', 'fuss', 'fuß', 'fahrrad', 'foot', 'car', 'bike'];


    public static function checkString(string $haystack): array {
        $matches = [];

        foreach (self::TRIGGER_WORDS as $triggerWord) {
            if (str_contains(strtolower($haystack), $triggerWord)) {
                $matches[] = $triggerWord;
            }
        }

        return $matches;
    }

    public static function checkAndReport(string $haystack, ReportableSubject $subjectType, int $subjectId): void {
        $matches = self::checkString($haystack);

        $info = sprintf('Automatically reported: The %s is inappropriate because it contains the words "%s".', $subjectType->value, implode('", "', $matches));
        Log::info($info);

        (new ReportRepository())->createReport(
            subjectType: $subjectType,
            subjectId:   $subjectId,
            reason:      ReportReason::INAPPROPRIATE,
            description: $info,
        );
    }
}
