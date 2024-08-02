<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Models\TrustedUser as TrustedUserModel;
use Illuminate\Console\Command;

class TrustedUser extends Command
{
    protected $signature   = 'app:clean-db:trusted-user';
    protected $description = 'Find and delete expired trusted users from database';

    public function handle(): int {
        $affectedRows = TrustedUserModel::where('expires_at', '<', now())->delete();
        $this->info($affectedRows . ' expired trusted users deleted.');
        return 0;
    }
}
