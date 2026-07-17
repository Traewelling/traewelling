<?php

namespace App\Observers;

use App\Enum\WebhookEvent;
use App\Events\StatusDeleteEvent;
use App\Helpers\CacheKey;
use App\Http\Controllers\Backend\Support\MentionHelper;
use App\Http\Controllers\Backend\WebhookController;
use App\Jobs\DeleteStatusNotifications;
use App\Models\Status;

class StatusObserver
{
    public function created(Status $status): void
    {
        CacheKey::increment(CacheKey::STATUS_CREATED);
        MentionHelper::createMentions($status);
    }

    public function updated(Status $status): void
    {
        MentionHelper::createMentions($status);
    }

    public function deleting(Status $status): void
    {
        // the checkin is cascade-deleted with the status on database level:
        // load it now, so listeners of the deleted event can still access it
        $status->loadMissing('checkin');
    }

    public function deleted(Status $status): void
    {
        CacheKey::increment(CacheKey::STATUS_DELETED);

        WebhookController::sendStatusWebhook(
            status: $status,
            event: WebhookEvent::CHECKIN_DELETE
        );

        DeleteStatusNotifications::dispatch($status->id);
        StatusDeleteEvent::dispatch($status);
    }
}
