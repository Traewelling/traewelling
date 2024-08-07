<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enum\Report\ReportStatus;
use App\Exceptions\TelegramException;
use App\Models\Report;
use App\Services\TelegramService;
use Illuminate\Support\Facades\App;

class ReportObserver
{
    public function created(Report $report): void {
        if (App::runningUnitTests() || !TelegramService::isAdminActive()) {
            return;
        }
        try {
            $telegramMessageId = TelegramService::admin()->sendMessage("<b>🚨 New Report for " . $report->subject_type . "</b>" . PHP_EOL
                                                                       . "Reason: " . $report->reason?->value . PHP_EOL
                                                                       . "Description: " . ($report->description ?? 'None') . PHP_EOL
                                                                       . "View Report: " . config('app.url') . "/admin/reports/" . $report->id . PHP_EOL);
            $report->update(['admin_notification_id' => $telegramMessageId]);
        } catch (TelegramException $exception) {
            report($exception);
        }
    }

    public function updated(Report $report): void {
        if (App::runningUnitTests() || !TelegramService::isAdminActive()) {
            return;
        }
        $statusBefore = $report->getOriginal('status');
        $statusAfter  = $report->status;

        if ($statusBefore === ReportStatus::OPEN && $statusAfter === ReportStatus::CLOSED && $report->admin_notification_id) {
            TelegramService::admin()->deleteMessage($report->admin_notification_id);
        }
    }
}
