<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Http\Resources\StatusResource;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;

class StatusExporter extends AbstractExporter
{
    protected string $fileName = 'statuses.json';

    protected function exportData(): array|string
    {
        $statuses = $this->user->statuses()->with('tags', 'ticket');

        return StatusResource::collection($statuses->get())->toJson();
    }

    protected function onExportValidation(): bool
    {
        return true;
    }
}
