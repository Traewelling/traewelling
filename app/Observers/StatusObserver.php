<?php

namespace App\Observers;

use App\Enum\WebhookEvent;
use App\Helpers\CacheKey;
use App\Http\Controllers\Backend\Support\MentionHelper;
use App\Http\Controllers\Backend\WebhookController;
use App\Models\Status;
use App\Notifications\StatusLiked;
use App\Notifications\UserJoinedConnection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Cache;

class StatusObserver
{
    public function created(Status $status): void {
        Cache::increment(CacheKey::STATUS_CREATED);
        MentionHelper::createMentions($status);
    }

    public function updated(Status $status): void {
        MentionHelper::createMentions($status);
    }

    public function deleted(Status $status): void {
        Cache::increment(CacheKey::STATUS_DELETED);

        WebhookController::sendStatusWebhook(
            status: $status,
            event:  WebhookEvent::CHECKIN_DELETE
        );

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
