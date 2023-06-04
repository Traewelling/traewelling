<?php

namespace App\Observers;

use App\Models\Status;
use App\Notifications\StatusLiked;
use App\Notifications\UserJoinedConnection;
use Illuminate\Notifications\DatabaseNotification;

class StatusObserver
{

    public function deleted(Status $status): void {
        // Delete all UserJoinedConnection-Notifications for this Status
        DatabaseNotification::where('type', UserJoinedConnection::class)
                            ->where('data->status->id', $status->id)
                            ->delete();

        // Delete all StatusLiked-Notifications for this Status
        DatabaseNotification::where('type', StatusLiked::class)
                            ->where('data->status->id', $status->id)
                            ->delete();
    }
}
