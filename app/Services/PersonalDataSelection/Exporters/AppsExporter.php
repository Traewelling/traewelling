<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

/**
 * This class is responsible for exporting the OAuth clients the user has created. NOT the ones the user has authorized.
 */
class AppsExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'apps.json';

    protected string $relation = 'oAuthClients';

    protected array $columns = [
        'id',
        'user_id',
        'name',
        'grant_types',
        'provider',
        'redirect',
        'personal_access_client',
        'password_client',
        'revoked',
        'created_at',
        'updated_at',
        'authorized_webhook_url',
        'privacy_policy_url',
        'webhooks_enabled',
    ];
}
