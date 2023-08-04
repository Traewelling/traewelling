<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class CleanUpNotifications extends Command
{

    protected $signature   = 'trwl:cleanUpNotifications';
    protected $description = 'Remove old notifications from the database';

    public function handle(): int {
        $this->info("Removing old notifications...");
        $affectedRows = DatabaseNotification::where('read_at', '<', now()->subDays(30))->delete();
        $this->info("Removed $affectedRows old and read notifications.");
        return Command::SUCCESS;
    }
}
