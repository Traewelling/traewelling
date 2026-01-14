<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class SessionExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'sessions.json';

    protected string $relation = 'sessions';

    protected array $columns = ['user_id', 'ip_address', 'user_agent', 'last_activity'];
}
