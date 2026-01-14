<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Models\EventSuggestion;
use Illuminate\Console\Command;

class CleanUpEventSuggestions extends Command
{
    protected $signature = 'app:clean-up-event-suggestions';

    protected $description = 'Delete all event suggestions with begin older than 30 days';

    public function handle(): int
    {
        $this->info('Cleaning up event suggestions older than 30 days...');

        // Assuming you have a model named EventSuggestion
        $deletedCount = EventSuggestion::where('begin', '<', now()->subDays(30))->delete();

        if ($deletedCount) {
            $this->info("Deleted $deletedCount old event suggestions.");
        } else {
            $this->info('No old event suggestions found to delete.');
        }

        return 0;
    }
}
