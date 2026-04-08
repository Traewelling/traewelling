<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Webhook;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;

class ResetWebhookFailureCountListener
{
    public function handle(WebhookCallSucceededEvent $event): void
    {
        $webhookId = $event->headers['X-Trwl-Webhook-Id'] ?? null;
        if ($webhookId === null) {
            return;
        }

        Webhook::where('id', $webhookId)
            ->where('consecutive_failures', '>', 0)
            ->update(['consecutive_failures' => 0]);
    }
}
