<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Models\EventSuggestion;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\ModelExportable;

class EventSuggestionsExporter extends AbstractExporter
{
    use ModelExportable;

    protected string $fileName    = 'event_suggestions.json';
    protected string $model       = EventSuggestion::class;
    protected string $whereColumn = 'user_id';
}
