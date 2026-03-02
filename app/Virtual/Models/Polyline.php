<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Polyline',
    description: 'Polyline of a single status as GeoJSON Feature',
    xml: new OA\Xml(name: 'Polyline'),
)]
class Polyline
{
    #[OA\Property(
        title: 'type',
        type: 'string',
        example: 'Feature',
    )]
    private string $type;

    #[OA\Property(
        property: 'geometry',
        type: 'object',
        properties: [
            new OA\Property(property: 'type', type: 'string', example: 'LineString'),
            new OA\Property(
                property: 'coordinates',
                type: 'array',
                items: new OA\Items(example: '[[8.39767,49.01625],[8.45947,49.06576],[8.52401,49.01625],[8.39218,48.88729],[8.25759,49.00544],[8.30703,49.07476],[8.39080,49.01535]]'),
            ),
        ],
    )]
    private mixed $geometry;

    #[OA\Property(
        property: 'properties',
        type: 'object',
        properties: [
            new OA\Property(property: 'statusId', type: 'integer', example: 1337),
        ],
    )]
    private mixed $properties;
}
