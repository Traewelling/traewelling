<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enum\Report\ReportStatus;
use App\Jobs\AdminNotification\DeleteAdminNotification;
use App\Jobs\AdminNotification\SendAdminNotification;
use App\Models\Report;

class ReportObserver
{
    public function created(Report $report): void
    {
        SendAdminNotification::dispatch(
            type: 'reports',
            htmlMessage: '<b>🚨 New Report for ' . $report->subject_type . '</b>' . PHP_EOL
                         . 'Reason: ' . $report->reason?->value . PHP_EOL
                         . 'Description: ' . ($report->description ?? 'None') . PHP_EOL
                         . 'View Report: ' . config('app.url') . '/admin/reports/' . $report->id . PHP_EOL,
            model: $report,
        );
    }

    public function updated(Report $report): void
    {
        $statusBefore = $report->getOriginal('status');
        $statusAfter = $report->status;

        if ($statusBefore === ReportStatus::OPEN && $statusAfter === ReportStatus::CLOSED) {
            DeleteAdminNotification::dispatch(
                type: 'reports',
                telegramId: $report->telegram_notification_id,
                matrixEventId: $report->matrix_notification_id,
            );
        }
    }
}
