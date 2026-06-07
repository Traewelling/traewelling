<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Models\ContributionHistory;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\ModelExportable;

class ContributionHistoryExporter extends AbstractExporter
{
    use ModelExportable;

    protected string $fileName = 'contribution_history_exporter.json';

    protected string $model = ContributionHistory::class;

    protected string $whereColumn = 'user_id';

    protected array $columns = [
        'id',
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'xp_change',
        'level_before',
        'level_after',
        'note',
        'created_at',
        'updated_at',
    ];
}
