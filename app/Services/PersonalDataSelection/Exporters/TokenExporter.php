<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class TokenExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'tokens.json';

    protected string $relation = 'tokens';

    protected array $columns = [
        'user_id',
        'client_id',
        'name',
        'scopes',
        'revoked',
        'expires_at',
        'created_at',
        'updated_at',
    ];
}
