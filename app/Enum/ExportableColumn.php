<?php
declare(strict_types=1);

namespace App\Enum;

enum ExportableColumn: string
{

    case STATUS_ID               = 'status_id';
    case JOURNEY_TYPE            = 'journey_type';
    case LINE_NAME               = 'line_name';
    case JOURNEY_NUMBER          = 'journey_number';
    case ORIGIN_NAME             = 'origin_name';
    case ORIGIN_COORDINATES      = 'origin_coordinates';
    case DEPARTURE_PLANNED       = 'departure_planned';
    case DEPARTURE_REAL          = 'departure_real';
    case DESTINATION_NAME        = 'destination_name';
    case DESTINATION_COORDINATES = 'destination_coordinates';
    case ARRIVAL_PLANNED         = 'arrival_planned';
    case ARRIVAL_REAL            = 'arrival_real';
    case DURATION                = 'duration';
    case DISTANCE                = 'distance';
    case POINTS                  = 'points';
    case BODY                    = 'body';
    case TRAVEL_TYPE             = 'travel_type';
    case STATUS_TAGS             = 'status_tags';
    case OPERATOR                = 'operator';

    public function title(): string {
        $title = __('export.title.' . $this->value);
        if (str_starts_with($title, 'export.title.')) {
            return $this->value;
        }
        return $title;
    }
}
