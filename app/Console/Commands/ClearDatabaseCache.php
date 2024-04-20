<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDatabaseCache extends Command
{

    protected $signature   = 'cache:clear-database';
    protected $description = 'Clear the database cache and deletes expired cache keys';

    public function handle(): int {
        $this->info('Clearing database cache...');
        $this->output->writeln('');
        $affectedRows = 0;
        do {
            $result = DB::table('cache')
                        ->where('expiration', '<', now()->timestamp)
                        ->limit(1000)
                        ->delete();
            if ($result > 0) {
                $this->output->write('.');
                $affectedRows += $result;
            }
        } while ($result > 0);
        $this->info("Deleted {$affectedRows} expired cache keys");
        return Command::SUCCESS;
    }
}
