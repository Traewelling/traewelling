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
            $results = DatabaseNotification::where('read_at', '<', now()->subDays(30))->limit(1000)->delete();
            $affectedRows += $results;
            if ($results > 0) {
                $this->output->write('.');
            }
        } while ($results > 0);
        $this->output->writeln('');
        $this->info("Removed $affectedRows old and read notifications.");

        $affectedRows = 0;
        $this->output->writeln('');
        do {
            $results = DatabaseNotification::where('read_at', '<', now()->subMonths(6))->limit(1000)->delete();
            if ($results > 0) {
                $this->output->write('.');
            }
        } while ($results > 0);
        $this->info("Removed $affectedRows old and unread notifications.");

        return Command::SUCCESS;
    }
}
