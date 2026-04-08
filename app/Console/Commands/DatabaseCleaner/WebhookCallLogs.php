<?php

declare(strict_types=1);

namespace App\Console\Commands\DatabaseCleaner;

use App\Models\WebhookCallLog;
use Illuminate\Console\Command;

class WebhookCallLogs extends Command
{
    protected $signature = 'app:clean-db:webhook-call-logs';

    protected $description = 'Remove webhook call logs older than 7 days';

    public function handle(): int
    {
        $this->info('Removing old webhook call logs...');
        $affectedRows = 0;
        do {
            $results = WebhookCallLog::where('created_at', '<', now()->subDays(7))->limit(1000)->delete();
            $affectedRows += $results;
            if ($results > 0) {
                $this->output->write('.');
            }
        } while ($results > 0);
        $this->output->writeln('');
        $this->info("Removed $affectedRows old webhook call logs.");

        return self::SUCCESS;
    }
}
