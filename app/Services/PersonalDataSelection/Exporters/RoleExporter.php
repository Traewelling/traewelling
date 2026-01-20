<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class RoleExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'roles.json';

    protected string $relation = 'roles';

    protected array $columns = [
        'name', 'guard_name', 'created_at', 'updated_at',
    ];
}
