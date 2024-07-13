<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Repositories\ReportRepository;
use Illuminate\Support\Facades\Log;

class ReportService
{
    private ReportRepository $reportRepository;
    private array            $triggerWords;

    public function __construct(?array $triggerWords = null, ?ReportRepository $reportRepository = null) {
        $this->reportRepository = $reportRepository ?? new ReportRepository();
        $this->triggerWords     = $triggerWords ?? ['auto', 'fuss', 'fuß', 'fahrrad', 'foot', 'car', 'bike'];
    }

    public function checkString(string $haystack): array {
        $matches = [];

        foreach ($this->triggerWords as $triggerWord) {
            if (str_contains(strtolower($haystack), $triggerWord)) {
                $matches[] = $triggerWord;
            }
        }

        return $matches;
    }

    public function checkAndReport(string $haystack, ReportableSubject $subjectType, int $subjectId): void {
        $matches = $this->checkString($haystack);

        if ($matches === []) {
            return;
        }

        $info = sprintf('Automatically reported: The %s is inappropriate because it contains the words "%s".', $subjectType->value, implode('", "', $matches));
        Log::info($info);

        $this->reportRepository->createReport(
            $subjectType,
            $subjectId,
            ReportReason::INAPPROPRIATE,
            $info,
        );
    }
}
