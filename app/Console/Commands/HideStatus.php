<?php

namespace App\Console\Commands;

use App\Enum\StatusVisibility;
use App\Models\Status;
use App\Models\User;
use Illuminate\Console\Command;

class HideStatus extends Command
{
    protected $signature = 'trwl:hideStatus';

    public function handle(): int {

        $usersToHideFor = User::whereNotNull('privacy_hide_days')->get();

        foreach ($usersToHideFor as $user) {
            $this->info('Hiding statuses for user: ' . $user->username);
            $rows = Status::where('user_id', $user->id)
                          ->where('visibility', '!=', StatusVisibility::PRIVATE->value)
                          ->where('created_at', '<', now()->subDays($user->privacy_hide_days))
                          ->update(['visibility' => StatusVisibility::PRIVATE]);
            $this->info('Hid ' . $rows . ' statuses');
        }

        return 0;
    }
}
