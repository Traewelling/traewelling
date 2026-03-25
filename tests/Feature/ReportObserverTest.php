<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enum\Report\ReportStatus;
use App\Jobs\AdminNotification\DeleteAdminNotification;
use App\Jobs\AdminNotification\SendAdminNotification;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\FeatureTestCase;

class ReportObserverTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_creating_report_dispatches_send_notification_job(): void
    {
        Queue::fake();

        Report::create([
            'status' => ReportStatus::OPEN,
            'subject_type' => 'App\\Models\\User',
            'subject_id' => 1,
        ]);

        Queue::assertPushed(SendAdminNotification::class, fn ($job) => true);
    }

    public function test_closing_report_dispatches_delete_notification_job(): void
    {
        Queue::fake();

        $report = Report::create([
            'status' => ReportStatus::OPEN,
            'subject_type' => 'App\\Models\\User',
            'subject_id' => 1,
        ]);

        Queue::fake();

        $report->update(['status' => ReportStatus::CLOSED]);

        Queue::assertPushed(DeleteAdminNotification::class);
    }

    public function test_updating_report_to_non_closed_status_does_not_dispatch_delete(): void
    {
        Queue::fake();

        $report = Report::create([
            'status' => ReportStatus::OPEN,
            'subject_type' => 'App\\Models\\User',
            'subject_id' => 1,
        ]);

        Queue::fake();

        $report->update(['status' => ReportStatus::WAITING]);

        Queue::assertNotPushed(DeleteAdminNotification::class);
    }
}
