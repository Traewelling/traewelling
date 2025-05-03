<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\DatabaseExportable;

class ActivityLogExporter extends AbstractExporter
{
    use DatabaseExportable;

    protected string $fileName        = 'activity_log.json';
    protected string $tableName       = 'activity_log';
    protected array  $columns         = [
        'id', 'log_name', 'description', 'subject_type', 'event', 'subject_id',
        'causer_type', 'causer_id', 'properties', 'created_at', 'updated_at'
    ];
    protected string $whereColumn     = 'causer_id';
    protected array  $whereConditions = ['causer_type' => 'App\Models\User'];
}
