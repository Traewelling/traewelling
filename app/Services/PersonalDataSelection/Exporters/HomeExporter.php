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

    protected array $columns = [
        'id',
        'name',
        'latitude',
        'longitude',
        'created_at',
        'updated_at',
        'time_offset',
        'shift_time',
        'source',
        'relevance',
    ];
}
