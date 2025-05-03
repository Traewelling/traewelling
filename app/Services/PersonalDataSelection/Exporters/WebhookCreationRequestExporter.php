<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Models\WebhookCreationRequest;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\ModelExportable;

class WebhookCreationRequestExporter extends AbstractExporter
{
    use ModelExportable;

    protected string $fileName    = 'webhook_creation_requests.json';
    protected string $model       = WebhookCreationRequest::class;
    protected string $whereColumn = 'user_id';
}
