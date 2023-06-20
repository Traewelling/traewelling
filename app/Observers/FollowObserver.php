<?php

namespace App\Observers;

use App\Models\Follow;
use App\Notifications\UserFollowed;
use Illuminate\Notifications\DatabaseNotification;

class FollowObserver
{

    public function deleted(Follow $follow): void {
        //delete all UserFollowed notifications between these users
        DatabaseNotification::where('type', UserFollowed::class)
                            ->where('notifiable_id', $follow->follow_id)
                            ->where('data->follower->id', $follow->user_id)
                            ->delete();
    }
}
