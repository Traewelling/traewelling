<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Models\User;
use Illuminate\Console\Command;

class SpamUser extends Command
{
    protected $signature   = 'app:clean-db:spam-user';
    protected $description = 'Delete users who have registered but have not agreed to the privacy policy';

    public function handle(): int {
        $affectedRows = 0;
        $this->info('Deleting users who have not agreed to the privacy policy...');
        $this->output->writeln('');
        do {
            $result = User::where('privacy_ack_at', null)
                                ->where('created_at', '<', now()->subDay())
                                ->limit(1000)
                                ->delete();
            if ($result > 0) {
                $affectedRows += $result;
                $this->output->write('.');
            }
        } while ($result < 0);
        $this->info($affectedRows . ' users deleted.');

        return 0;
    }
}
