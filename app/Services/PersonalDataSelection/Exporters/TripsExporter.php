<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\DatabaseExportable;

class TripsExporter extends AbstractExporter
{
    use DatabaseExportable;

    protected string $fileName = 'trips.json';

    protected string $tableName = 'hafas_trips';

    protected array $columns = [
        'id', 'trip_id', 'category', 'number', 'linename', 'journey_number',
        'operator_id', 'origin_id', 'destination_id', 'polyline_id', 'departure', 'arrival',
        'source', 'motis_source', 'user_id', 'last_refreshed', 'created_at', 'updated_at', 'route_color', 'mode', 'route_text_color', 'continuation_trip_id', 'motis_source_license_id',
    ];

    protected string $whereColumn = 'user_id';
}
