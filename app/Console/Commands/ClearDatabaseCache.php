<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDatabaseCache extends Command
{

    protected $signature   = 'cache:clear-database';
    protected $description = 'Clear the database cache and deletes expired cache keys';

    public function handle(): int {
        $affectedRows = DB::table('cache')->where('expiration', '<', now()->timestamp)->delete();
        $this->info("Deleted {$affectedRows} expired cache keys");
        return Command::SUCCESS;
    }
}
