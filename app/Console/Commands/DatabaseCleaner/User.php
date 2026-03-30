<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Models\User as UserModel;
use Illuminate\Console\Command;

class User extends Command
{
    protected $signature = 'app:clean-db:user';

    protected $description = 'Delete users who have registered but have not agreed to the privacy policy';

    public function handle(): int
    {
        $affectedRows = 0;
        $this->info('Deleting users who have not agreed to the privacy policy...');
        $this->output->writeln('');
        do {
            $result = UserModel::leftJoin('privacy_policy_acceptances', 'users.uuid', '=', 'privacy_policy_acceptances.user_id')
                ->whereNull('privacy_policy_acceptances.user_id')
                ->where('users.created_at', '<', now()->subDay())
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
