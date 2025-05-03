<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\DatabaseExportable;

class ReportsExporter extends AbstractExporter
{
    use DatabaseExportable;

    protected string $fileName    = 'reports.json';
    protected string $tableName   = 'reports';
    protected array  $columns     = [
        'subject_type', 'subject_id', 'reason', 'description', 'reporter_id'
    ];
    protected string $whereColumn = 'reporter_id';
}
