<?php declare(strict_types=1);

namespace App\Services;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Models\Report;
use App\Models\User;

abstract class ReportService
{

    public static function createReport(
        ReportableSubject $subjectType,
        string|int        $subjectId,
        ReportReason      $reason,
        ?string           $description = null,
        User              $reporter = null,
    ): void {
        Report::create([
                           'subject_type' => 'App\\Models\\' . $subjectType->value,
                           'subject_id'   => $subjectId,
                           'reason'       => $reason->value,
                           'description'  => $description ?? null,
                           'reporter_id'  => $reporter?->id,
                       ]);
    }

}
