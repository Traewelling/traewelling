<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class StatusExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'statuses.json';

    protected string $relation = 'statuses';

    protected string $with = 'tags';
    // todo: columns
}
