<?php

namespace App\Listeners;

use App\Enum\CacheKey;
use App\Enum\MonitoringCounter;
use App\Models\Webhook;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;

class RemoveAbsentWebhooksListener
{
    public function handle(WebhookCallFailedEvent $event) {
        if (!$event->response) {
            return;
        }

        if ($event->response->getStatusCode() !== 410) {
            return;
        }

        $webhookId = $event->headers["X-Trwl-Webhook-Id"];
        Webhook::findOrFail($webhookId)->delete();
        Log::info("Deleted Webhook {webhookId} from User {userId} because server has sent 410 Gone response.", [
            "webhookId" => $webhookId,
            "userId"    => $event->headers["X-Trwl-User-Id"]
        ]);
        Cache::increment(CacheKey::getMonitoringCounterKey(MonitoringCounter::WebhookAbsent));
    }
}
