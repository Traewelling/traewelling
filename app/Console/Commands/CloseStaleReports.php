<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enum\Report\ReportStatus;
use App\Repositories\ReportRepository;
use Illuminate\Console\Command;

class CloseStaleReports extends Command
{
    protected $signature = 'app:close-stale-reports';

    protected $description = 'Close open reports that have not been processed for 14 days';

    private const int INACTIVE_DAYS = 14;

    public function handle(ReportRepository $reportRepository): int
    {
        $reports = $reportRepository->getOpenReportsWithoutProcessingSince(now()->subDays(self::INACTIVE_DAYS));

        foreach ($reports as $report) {
            activity()
                ->performedOn($report)
                ->withProperties([
                    'attributes' => ['status' => ReportStatus::CLOSED],
                    'old' => ['status' => $report->status],
                ])
                ->log('Automatically closed after ' . self::INACTIVE_DAYS . ' days without processing');

            $report->update(['status' => ReportStatus::CLOSED]);
        }

        $this->info("Closed {$reports->count()} stale reports.");

        return Command::SUCCESS;
    }
}
