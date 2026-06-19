<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Http\Resources\WebhookResource;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;

class WebhookExporter extends AbstractExporter
{
    protected string $fileName = 'webhooks.json';

    protected function exportData(): array
    {
        $webhooks = $this->user->webhooks()->with('events');

        return WebhookResource::collection($webhooks->get())->toArray(request());
    }

    protected function onExportValidation(): bool
    {
        return true;
    }
}
