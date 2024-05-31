<?php

namespace App\Console\Commands\DatabaseCleaner;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class Notifications extends Command
{

    protected $signature   = 'app:clean-db:notifications';
    protected $description = 'Remove old notifications from the database';

    public function handle(): int {
        $this->info("Removing old notifications...");
        $affectedRows = 0;
        do {
            $results      = DatabaseNotification::where('created_at', '<', now()->subDays(14))->limit(1000)->delete();
            $affectedRows += $results;
            if ($results > 0) {
                $this->output->write('.');
            }
        } while ($results > 0);
        $this->output->writeln('');
        $this->info("Removed $affectedRows old notifications.");

        return parent::SUCCESS;
    }
}
