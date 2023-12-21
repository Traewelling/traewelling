<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanUpUsers extends Command
{
    protected $signature   = 'trwl:cleanUpUsers';
    protected $description = 'Delete users who have registered but have not agreed to the privacy policy';

    public function handle(): int {
        $privacyUsers = User::where('privacy_ack_at', null)
                            ->where('created_at', '<', now()->subDay())
                            ->get();
        foreach ($privacyUsers as $user) {
            $user->delete();
        }
        return 0;
    }
}
