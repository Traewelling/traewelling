<?php

namespace App\Console\Commands;

use App\Models\OAuthClient;
use App\Models\Webhook;
use App\Notifications\WebhookDisabled;
use App\Rules\SecureWebhookUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class DisableInsecureWebhooks extends Command
{
    protected $signature = 'app:disable-insecure-webhooks';

    protected $description = 'Disable webhooks and OAuth client webhook URLs pointing to non-public addresses';

    public function handle(): void
    {
        $clearedClients = 0;

        OAuthClient::query()
            ->whereNotNull('authorized_webhook_url')
            ->chunkById(500, function (Collection $clients) use (&$clearedClients): void {
                foreach ($clients as $client) {
                    if (SecureWebhookUrl::isAllowedUrl($client->authorized_webhook_url)) {
                        continue;
                    }

                    $client->update([
                        'authorized_webhook_url' => null,
                        'webhooks_enabled' => false,
                    ]);
                    $clearedClients++;

                    $this->info("Removed webhook configuration of client #{$client->id} ({$client->name}).");
                }
            });

        $disabledWebhooks = 0;

        Webhook::query()
            ->whereNull('disabled_at')
            ->chunkById(500, function (Collection $webhooks) use (&$disabledWebhooks): void {
                foreach ($webhooks as $webhook) {
                    if (SecureWebhookUrl::isAllowedUrl($webhook->url)) {
                        continue;
                    }

                    $webhook->update(['disabled_at' => now()]);
                    $disabledWebhooks++;

                    $this->info("Disabled webhook #{$webhook->id} ({$webhook->url}).");

                    $webhook->user->notify(new WebhookDisabled($webhook));
                }
            });

        $this->info("Disabled {$disabledWebhooks} webhooks and removed the webhook configuration of {$clearedClients} OAuth clients.");
    }
}
