<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\StatusLiked;
use Illuminate\Notifications\DatabaseNotification;

class UserObserver
{

    public function deleted(User $user): void {
        //delete all notifications for this user (there is no cascade delete)
        DatabaseNotification::where('notifiable_id', $user->id)
                            ->where('notifiable_type', User::class)
                            ->delete();

        //delete all liked notifications for this user
        DatabaseNotification::where('type', StatusLiked::class)
                            ->where('data->liker->id', $user->id)
                            ->delete();

        //TODO: extend this to delete all notifications for this user
    }
}
