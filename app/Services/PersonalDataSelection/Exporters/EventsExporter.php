<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Models\Event;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\ModelExportable;

/**
 * This class is responsible for exporting the events the user has approved.
 */
class EventsExporter extends AbstractExporter
{
    use ModelExportable;

    protected string $fileName = 'events.json';

    protected string $model = Event::class;

    protected string $whereColumn = 'approved_by';

    protected array $columns = [
        'id',
        'station_id',
        'name',
        'slug',
        'hashtag',
        'host',
        'url',
        'created_at',
        'updated_at',
        'event_start',
        'event_end',
        'approved_by',
        'checkin_start',
        'checkin_end',
    ];
}
