<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Webhook;
use App\Models\WebhookCallLog;
use Spatie\WebhookServer\Events\WebhookCallEvent;

class LogWebhookCallListener
{
    public function handle(WebhookCallEvent $event): void
    {
        $webhookId = $event->headers['X-Trwl-Webhook-Id'] ?? null;
        $userId = $event->headers['X-Trwl-User-Id'] ?? null;

        if ($webhookId === null || $userId === null) {
            return;
        }

        $webhook = Webhook::find($webhookId);
        if ($webhook === null) {
            return;
        }

        $payload = $event->payload;
        if (is_string($payload)) {
            $payload = json_decode($payload, true) ?? [];
        }

        WebhookCallLog::create([
            'webhook_id' => $webhookId,
            'user_id' => $userId,
            'oauth_client_id' => $webhook->oauth_client_id,
            'event' => $payload['event'] ?? 'unknown',
            'url' => $event->webhookUrl,
            'attempt' => $event->attempt,
            'response_code' => $event->response?->getStatusCode(),
            'created_at' => now(),
        ]);
    }
}
