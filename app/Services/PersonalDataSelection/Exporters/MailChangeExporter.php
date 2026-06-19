<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class MailChangeExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'mail_changes.json';

    protected string $relation = 'mailChanges';

    protected array $columns = [
        'id',
        'user_id',
        'old_email',
        'new_email',
        'created_at',
        'updated_at',
    ];
}
