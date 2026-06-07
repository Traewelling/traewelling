<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class NotificationsExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'notifications.json';

    protected string $relation = 'notifications';

    protected array $columns = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'created_at',
        'updated_at',
    ];
}
