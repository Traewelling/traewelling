<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;

class WebhookExporter extends AbstractExporter
{
    protected string $fileName = 'webhooks.json';

    protected function exportData(): array|string {
        $webhooks = $this->user->webhooks()->with('events')->get();
        $webhooks = $webhooks->map(function($webhook) {
            return $webhook->only([
                                      'oauth_client_id', 'created_at', 'updated_at'
                                  ]);
        });

        return $webhooks->toJson();
    }

    protected function onExportValidation(): bool {
        return true;
    }
}
