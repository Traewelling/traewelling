<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\DatabaseExportable;

/**
 * This class is responsible for exporting the blocks the user has made.
 */
class BlocksExporter extends AbstractExporter
{
    use DatabaseExportable;

    protected string $fileName = 'blocks.json';

    protected string $tableName = 'user_blocks';

    protected array $columns = [
        'id', 'user_id', 'blocked_id', 'created_at', 'updated_at',
    ];

    protected string $whereColumn = 'user_id';
}
