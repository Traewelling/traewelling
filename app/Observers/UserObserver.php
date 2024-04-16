<?php

namespace App\Observers;

use App\Enum\CacheKey;
use App\Models\User;
use App\Notifications\StatusLiked;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    public function created(User $user): void {
        Cache::increment(CacheKey::USER_CREATED);
    }

    public function deleted(User $user): void {
        Cache::increment(CacheKey::USER_DELETED);

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
