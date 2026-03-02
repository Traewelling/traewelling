<?php

declare(strict_types=1);

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

/** Shared OA schema for Laravel pagination links. Not a real resource. */
#[OA\Schema(
    schema: 'Links',
    title: 'Links',
    description: 'Pagination links',
    properties: [
        new OA\Property(property: 'first', type: 'string', format: 'uri', example: 'https://traewelling.de/api/v1/ENDPOINT?page=1', nullable: true),
        new OA\Property(property: 'last', type: 'string', format: 'uri', example: null, nullable: true),
        new OA\Property(property: 'prev', type: 'string', format: 'uri', example: null, nullable: true),
        new OA\Property(property: 'next', type: 'string', format: 'uri', example: 'https://traewelling.de/api/v1/ENDPOINT?page=2', nullable: true),
    ],
)]
class LinksResource {}
