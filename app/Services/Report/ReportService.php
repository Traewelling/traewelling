<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use App\Models\Report;
use App\Models\User;
use App\Repositories\ReportRepository;

class ReportService
{
    public function __construct(private readonly ReportRepository $reportRepository = new ReportRepository()) {}

    /**
     * Repeated reports on the same subject end up in the reporter's open report.
     */
    public function report(
        ReportableSubject $subjectType,
        int $subjectId,
        ReportReason $reason,
        string $description,
        User $reporter,
    ): Report {
        $existing = $this->reportRepository->findUnresolvedReportBy($subjectType, $subjectId, $reporter);
        if ($existing === null) {
            return $this->reportRepository->createReport($subjectType, $subjectId, $reason, $description, $reporter);
        }

        if ($existing->description !== $description) {
            activity()->causedBy($reporter)->performedOn($existing)->log($description);
        }

        return $existing;
    }

    public function update(
        Report $report,
        User $admin,
        ReportStatus $status,
        ?string $note = null,
        bool $applyToSameSubject = false,
    ): void {
        $reports = collect([$report]);
        if ($applyToSameSubject) {
            $reports = $reports->merge($this->reportRepository->getUnresolvedReportsOnSameSubject($report));
        }

        foreach ($reports as $target) {
            $logger = activity()->causedBy($admin)->performedOn($target);
            if ($target->status !== $status) {
                $logger->withProperties([
                    'attributes' => ['status' => $status->value],
                    'old' => ['status' => $target->status->value],
                ]);
            }
            $logger->log($note ?? '');

            $target->update(['status' => $status]);
        }
    }
}
