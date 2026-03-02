<?php

declare(strict_types=1);

namespace App\Virtual\RequestBodies;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StatusUpdateBody',
    description: 'Status Update Body',
    xml: new OA\Xml(name: 'StatusUpdateBody'),
    properties: [
        new OA\Property(property: 'body', type: 'string', maxLength: 280, description: 'Status-Text to be displayed alongside the checkin', example: 'Wow. This train is extremely crowded!', nullable: true),
        new OA\Property(property: 'business', ref: '#/components/schemas/Business'),
        new OA\Property(property: 'visibility', ref: '#/components/schemas/StatusVisibility'),
        new OA\Property(property: 'eventId', type: 'string', description: 'The ID of the event this status is related to - or null', example: '1', nullable: true),
        new OA\Property(property: 'manualDeparture', type: 'string', format: 'date', description: 'Manual departure time set by the user', example: '2020-01-01 12:00:00', nullable: true),
        new OA\Property(property: 'manualArrival', type: 'string', format: 'date', description: 'Manual arrival time set by the user', example: '2020-01-01 13:00:00', nullable: true),
        new OA\Property(property: 'destinationId', type: 'string', description: 'Destination station id', example: '1', nullable: true),
        new OA\Property(property: 'destinationArrivalPlanned', type: 'string', format: 'date', description: 'Destination arrival time', example: '2020-01-01 13:00:00', nullable: true),
    ],
)]
class StatusUpdateBody {}
