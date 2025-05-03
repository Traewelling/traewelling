<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class IcsTokenExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'ics_tokens.json';
    protected string $relation = 'icsTokens';
    // todo: columns
}
