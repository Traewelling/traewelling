<?php

namespace App\Console\Commands\DatabaseCleaner;

use Illuminate\Console\Command;
use Laravel\Passport\Passport;

class CleanUpTokens extends Command
{
    protected $signature = 'app:clean-up-tokens';

    protected $description = 'Delete all tokens expired more than 30 days';

    public function handle(): int
    {
        $this->info('Cleaning up tokens older than 30 days...');

        // Assuming you have a model named Token
        $deletedCount = Passport::tokenModel()::where('expires_at', '<', now()->subDays(30))->delete();

        if ($deletedCount) {
            $this->info("Deleted $deletedCount old tokens.");
        } else {
            $this->info('No old tokens found to delete.');
        }

        return 0;
    }
}
