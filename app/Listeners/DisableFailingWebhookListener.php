<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Webhook;
use App\Notifications\WebhookDisabled;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;

class DisableFailingWebhookListener
{
    private const int FAILURE_THRESHOLD = 5;

    public function handle(FinalWebhookCallFailedEvent $event): void
    {
        $webhookId = $event->headers['X-Trwl-Webhook-Id'] ?? null;
        if ($webhookId === null) {
            return;
        }

        $webhook = Webhook::find($webhookId);
        if ($webhook === null || $webhook->disabled_at !== null) {
            return;
        }

        $webhook->increment('consecutive_failures');

        if ($webhook->consecutive_failures < self::FAILURE_THRESHOLD) {
            return;
        }

        $webhook->update(['disabled_at' => now()]);

        Log::warning('Webhook {webhookId} disabled after {failures} consecutive failures.', [
            'webhookId' => $webhook->id,
            'userId' => $webhook->user_id,
            'url' => $webhook->url,
            'failures' => $webhook->consecutive_failures,
        ]);

        $webhook->user->notify(new WebhookDisabled($webhook));
    }
}
