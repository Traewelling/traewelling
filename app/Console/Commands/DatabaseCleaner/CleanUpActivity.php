<?php

namespace App\Console\Commands\DatabaseCleaner;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class CleanUpActivity extends Command
{
    protected $signature = 'app:clean-up-activity';

    protected $description = 'Delete all activity logs older than 30 days';

    public function handle(): int
    {
        $this->info('Cleaning up activity logs older than 30 days...');

        // Assuming you have a model named ActivityLog
        $deletedCount = Activity::where('created_at', '<', now()->subDays(30))->delete();

        if ($deletedCount) {
            $this->info("Deleted $deletedCount old activity logs.");
        } else {
            $this->info('No old activity logs found to delete.');
        }

        return 0;
    }
}
