<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TwitterUnstable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class PostTwitterUnstableNotification extends Command
{
    protected $signature = 'trwl:postTwitterUnstable {user?*}';

    protected $description = 'Post a notification to users indicating that their login might be broken soon. Takes multiple user ids concatted with spaces.';

    public function handle() {
        $users = $this->argument('user');

        if (empty($users)) {
            $this->warn("Missing argument. Call the function with `php artisan trwl:postTwitterUnstable 23 42 1337`.");
            return Command::INVALID;
        }

        $users = User::findOrFail($users);

        // Delete old notifications if there were any
        $users->each(fn($user) => $user->notifications()->where("type", "=", TwitterUnstable::class)->delete());
        $users->each(fn($user) => $user->notify(new TwitterUnstable()));

        return Command::SUCCESS;
    }
}
