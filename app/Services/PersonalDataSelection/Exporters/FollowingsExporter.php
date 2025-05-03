<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\DatabaseExportable;

class FollowingsExporter extends AbstractExporter
{
    use DatabaseExportable;

    protected string $fileName    = 'followings.json';
    protected string $tableName   = 'follows';
    protected array  $columns     = ['id', 'user_id', 'follow_id', 'created_at', 'updated_at'];
    protected string $whereColumn = 'follow_id';
}
