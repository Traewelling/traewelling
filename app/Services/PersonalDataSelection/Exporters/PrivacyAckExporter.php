<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class PrivacyAckExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'privacy_policy_acceptances.json';

    protected string $relation = 'privacyPolicyAcceptances';

    protected array $columns = [
        'id',
        'user_id',
        'privacy_policy_id',
        'accepted_at',
        'created_at',
        'updated_at',
    ];
}
