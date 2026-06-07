<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Models\WebhookCallLog;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\ModelExportable;

class WebhookCallLogExporter extends AbstractExporter
{
    use ModelExportable;

    protected string $fileName = 'webhook_call_log.json';

    protected string $model = WebhookCallLog::class;

    protected string $whereColumn = 'user_id';

    protected array $columns = [
        'id',
        'webhook_id',
        'user_id',
        'oauth_client_id',
        'event',
        'attempt',
        'created_at',
    ];
}
