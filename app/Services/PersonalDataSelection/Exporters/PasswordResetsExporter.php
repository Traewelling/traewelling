<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\DatabaseExportable;

class PasswordResetsExporter extends AbstractExporter
{
    use DatabaseExportable;

    protected string $fileName       = 'password_resets.json';
    protected string $tableName      = 'password_resets';
    protected array  $columns        = [
        'email', 'created_at'
    ];
    protected string $whereColumn    = 'email';
    protected string $whereCondition = 'email';
}
