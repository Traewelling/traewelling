<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class PermissionExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'permissions.json';
    protected string $relation = 'permissions';
    // todo: columns
}
