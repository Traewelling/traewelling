<?php

namespace App\Observers;

use App\Models\Like;
use App\Notifications\StatusLiked;
use Illuminate\Notifications\DatabaseNotification;

class LikeObserver
{
    public function deleted(Like $like): void {
        //delete like notifications for this status
        DatabaseNotification::where('type', StatusLiked::class)
                            ->where('data->status->id', $like->status_id)
                            ->where('data->liker->id', $like->user_id)
                            ->delete();
    }
}
