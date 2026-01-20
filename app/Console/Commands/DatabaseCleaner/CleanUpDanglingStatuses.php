<?php

namespace App\Console\Commands\DatabaseCleaner;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanUpDanglingStatuses extends Command
{
    protected $signature = 'app:clean-db:dangling-statuses';

    protected $description = 'Delete Statuses that have no associated Checkins';

    public function handle(): int
    {
        $affectedRows = 0;
        $this->info('Deleting dangling statuses...');
        $this->output->writeln('');
        $bar = $this->output->createProgressBar();
        $bar->start();
        do {
            $result = DB::table('statuses')
                ->leftJoin('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                ->whereNull('train_checkins.status_id')
                ->limit(200)
                ->delete();

            if ($result > 0) {
                $affectedRows += $result;
                $bar->advance($result);
            }
        } while ($result > 0);

        $bar->finish();

        $this->info($affectedRows . ' dangling statuses deleted.');

        return 0;
    }
}
