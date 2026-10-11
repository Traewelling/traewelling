<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReportRepository
{
    public function createReport(
        ReportableSubject $subjectType,
        string|int $subjectId,
        ReportReason $reason,
        ?string $description = null,
        ?User $reporter = null,
    ): void {
        Report::create([
            'subject_type' => 'App\\Models\\' . $subjectType->value,
            'subject_id' => $subjectId,
            'reason' => $reason->value,
            'description' => $description ?? null,
            'reporter_id' => $reporter?->id,
        ]);
    }

    /**
     * Open reports that neither changed nor got an activity log entry (e.g. an admin comment) since the given time.
     *
     * @return Collection<int, Report>
     */
    public function getOpenReportsWithoutProcessingSince(CarbonInterface $since): Collection
    {
        return Report::where('status', ReportStatus::OPEN)
            ->where('updated_at', '<', $since)
            ->whereDoesntHave('activitiesAsSubject', function (Builder $query) use ($since): void {
                $query->where('created_at', '>=', $since);
            })
            ->get();
    }
}
