<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use App\Models\Report;
use App\Models\Status;
use App\Models\Trip;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\CursorPaginator;

class ReportRepository
{
    private const array UNRESOLVED_STATUSES = [ReportStatus::OPEN, ReportStatus::WAITING];

    public function createReport(
        ReportableSubject $subjectType,
        string|int $subjectId,
        ReportReason $reason,
        ?string $description = null,
        ?User $reporter = null,
    ): Report {
        return Report::create([
            'subject_type' => $this->subjectClass($subjectType),
            'subject_id' => $subjectId,
            'reason' => $reason->value,
            'description' => $description ?? null,
            'reporter_id' => $reporter?->id,
        ]);
    }

    public function findUnresolvedReportBy(ReportableSubject $subjectType, int $subjectId, User $reporter): ?Report
    {
        return Report::where('subject_type', $this->subjectClass($subjectType))
            ->where('subject_id', $subjectId)
            ->where('reporter_id', $reporter->id)
            ->whereIn('status', self::UNRESOLVED_STATUSES)
            ->first();
    }

    /**
     * Other open or waiting reports on the same subject as the given report.
     *
     * @return Collection<int, Report>
     */
    public function getUnresolvedReportsOnSameSubject(Report $report): Collection
    {
        return Report::where('subject_type', $report->subject_type)
            ->where('subject_id', $report->subject_id)
            ->whereKeyNot($report->id)
            ->whereIn('status', self::UNRESOLVED_STATUSES)
            ->get();
    }

    /**
     * @param  int|null  $subjectUserId  only reports on this user or on their statuses and trips
     * @return CursorPaginator<int, Report>
     */
    public function paginateOpenFirst(
        ?ReportStatus $status = null,
        ?ReportableSubject $subjectType = null,
        ?int $subjectId = null,
        ?int $subjectUserId = null,
        int $perPage = 15,
    ): CursorPaginator {
        $query = $this->withModerationContext(Report::query())
            ->selectRaw("(CASE status WHEN 'open' THEN 0 WHEN 'waiting' THEN 1 ELSE 2 END) AS status_rank")
            ->orderBy('status_rank')
            ->orderByDesc('created_at');

        if ($status !== null) {
            $query->where('status', $status);
        }
        if ($subjectType !== null && $subjectId !== null) {
            $query->where('subject_type', $this->subjectClass($subjectType))->where('subject_id', $subjectId);
        }
        if ($subjectUserId !== null) {
            $query->where(static function (Builder $query) use ($subjectUserId): void {
                $query->where(static fn (Builder $query) => $query->where('subject_type', User::class)->where('subject_id', $subjectUserId))
                    ->orWhere(static fn (Builder $query) => $query->where('subject_type', Status::class)
                        ->whereIn('subject_id', Status::select('id')->where('user_id', $subjectUserId)))
                    ->orWhere(static fn (Builder $query) => $query->where('subject_type', Trip::class)
                        ->whereIn('subject_id', Trip::select('id')->where('user_id', $subjectUserId)));
            });
        }

        return $query->cursorPaginate($perPage)->withQueryString();
    }

    public function getWithModerationContext(string $reportId): Report
    {
        return $this->withModerationContext(Report::query())
            ->with('activitiesAsSubject.causer')
            ->findOrFail($reportId);
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

    /**
     * @param  Builder<Report>  $query
     * @return Builder<Report>
     */
    private function withModerationContext(Builder $query): Builder
    {
        return $query
            ->select('reports.*')
            ->addSelect([
                'other_unresolved_reports' => Report::query()
                    ->from('reports', 'same_subject')
                    ->selectRaw('count(*)')
                    ->whereColumn('same_subject.subject_type', 'reports.subject_type')
                    ->whereColumn('same_subject.subject_id', 'reports.subject_id')
                    ->whereColumn('same_subject.id', '!=', 'reports.id')
                    ->whereIn('same_subject.status', self::UNRESOLVED_STATUSES),
            ])
            ->with([
                'reporter',
                'subject' => static function (MorphTo $morphTo): void {
                    $morphTo->morphWith([
                        Status::class => ['user', 'checkin.trip', 'checkin.originStopover.station', 'checkin.destinationStopover.station'],
                        Trip::class => ['user', 'originStation', 'destinationStation'],
                    ]);
                },
            ]);
    }

    private function subjectClass(ReportableSubject $subjectType): string
    {
        return 'App\\Models\\' . $subjectType->value;
    }
}
