<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class SocialProfileExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'social_profiles.json';
    protected string $relation = 'socialProfile';
    protected string $with     = 'mastodonserver';
    // todo: columns
}
