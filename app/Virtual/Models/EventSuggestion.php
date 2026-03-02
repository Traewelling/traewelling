<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'EventSuggestion',
    description: 'Fields for suggesting an event',
    xml: new OA\Xml(name: 'EventSuggestion'),
)]
class EventSuggestion
{
    #[OA\Property(
        title: 'name',
        description: 'name of the event',
        type: 'string',
        maxLength: 255,
        example: 'Eröffnung der Nebenbahn in Knuffingen',
    )]
    private string $name;

    #[OA\Property(
        title: 'host',
        description: 'host of the event',
        type: 'string',
        nullable: true,
        example: 'MiWuLa',
    )]
    private string $host;

    #[OA\Property(
        title: 'begin',
        description: 'Timestamp for the start of the event',
        type: 'string',
        example: '2022-06-01T00:00:00+02:00',
    )]
    private string $begin;

    #[OA\Property(
        title: 'end',
        description: 'Timestamp for the end of the event',
        type: 'string',
        example: '2022-08-31T23:59:00+02:00',
    )]
    private string $end;

    #[OA\Property(
        title: 'url',
        description: 'external URL for this event',
        type: 'string',
        maxLength: 255,
        nullable: true,
        example: 'https://www.bundesregierung.de/breg-de/aktuelles/faq-9-euro-ticket-2028756',
    )]
    private string $url;

    #[OA\Property(
        title: 'hashtag',
        description: 'hashtag for this event',
        type: 'string',
        maxLength: 40,
        nullable: true,
        example: 'gpn21',
    )]
    private string $hashtag;

    /** @deprecated Use nearestStationId instead */
    #[OA\Property(
        title: 'nearestStation',
        description: 'Query string for the nearest station to this event. Deprecated: use nearestStationId instead.',
        type: 'string',
        maxLength: 255,
        nullable: true,
        deprecated: true,
        example: 'Berlin Hbf',
    )]
    private string $nearestStation;

    #[OA\Property(
        title: 'nearestStationId',
        description: 'ID of the nearest station to this event',
        type: 'integer',
        nullable: true,
        example: 1,
    )]
    private int $nearestStationId;
}
