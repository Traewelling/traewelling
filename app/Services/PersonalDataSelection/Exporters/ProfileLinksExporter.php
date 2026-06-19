<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class ProfileLinksExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'profile_links.json';

    protected string $relation = 'profileLinks';

    protected array $columns = [
        'id',
        'user_id',
        'name',
        'url',
        'created_at',
        'updated_at',
    ];
}
