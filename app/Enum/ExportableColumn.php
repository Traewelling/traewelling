<?php

declare(strict_types=1);

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'ExportableColumn',
    description: 'Columns that can be exported in the export file.',
    type: 'string',
    enum: [
        'status_id',
        'journey_type',
        'line_name',
        'journey_number',
        'origin_name',
        'origin_coordinates',
        'departure_planned',
        'departure_real',
        'destination_name',
        'destination_coordinates',
        'arrival_planned',
        'arrival_real',
        'duration',
        'distance',
        'points',
        'body',
        'travel_type',
        'status_tags',
        'operator',
    ],
)]
enum ExportableColumn: string
{
    case STATUS_ID = 'status_id';
    case JOURNEY_TYPE = 'journey_type';
    case LINE_NAME = 'line_name';
    case JOURNEY_NUMBER = 'journey_number';
    case ORIGIN_NAME = 'origin_name';
    case ORIGIN_COORDINATES = 'origin_coordinates';
    case DEPARTURE_PLANNED = 'departure_planned';
    case DEPARTURE_REAL = 'departure_real';
    case DESTINATION_NAME = 'destination_name';
    case DESTINATION_COORDINATES = 'destination_coordinates';
    case ARRIVAL_PLANNED = 'arrival_planned';
    case ARRIVAL_REAL = 'arrival_real';
    case DURATION = 'duration';
    case DISTANCE = 'distance';
    case POINTS = 'points';
    case BODY = 'body';
    case TRAVEL_TYPE = 'travel_type';
    case STATUS_TAGS = 'status_tags';
    case OPERATOR = 'operator';

    public function title(): string
    {
        $title = __('export.title.' . $this->value);
        if (str_starts_with($title, 'export.title.')) {
            return $this->value;
        }

        return $title;
    }
}
