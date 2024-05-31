<?php

namespace App\Console\Commands\DatabaseCleaner;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FailedJobs extends Command
{
    protected $signature = 'app:clean-db:failed-jobs';

    public function handle(): int {
        $affectedRows = 0;
        $this->info('Delete failed jobs older than 14 days...');
        $this->output->writeln('');
        do {
            $result = DB::table('failed_jobs')
                        ->where('failed_at', '<', now()->subDays(14))
                        ->limit(1000)
                        ->delete();
            if ($result > 0) {
                $affectedRows += $result;
                $this->output->write('.');
            }
        } while ($result > 0);
        $this->output->writeln('');
        $this->info($affectedRows . ' rows deleted.');

        return 0;
    }
}
