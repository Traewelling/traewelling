<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\DatabaseExportable;

class TrustedUsersExporter extends AbstractExporter
{
    use DatabaseExportable;

    protected string $fileName    = 'trusted_users.json';
    protected string $tableName   = 'trusted_users';
    protected array  $columns     = [
        'id', 'user_id', 'trusted_id', 'expires_at', 'created_at', 'updated_at'
    ];
    protected string $whereColumn = 'user_id';
}
