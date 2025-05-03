<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\DatabaseExportable;

class MutesExporter extends AbstractExporter
{
    use DatabaseExportable;

    protected string $fileName    = 'mutes.json';
    protected string $tableName   = 'user_mutes';
    protected array  $columns     = [
        'id', 'user_id', 'muted_id', 'created_at', 'updated_at'
    ];
    protected string $whereColumn = 'user_id';
}
