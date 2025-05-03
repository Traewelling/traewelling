<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class HomeExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'home.json';
    protected string $relation = 'home';
    // todo: columns
}
