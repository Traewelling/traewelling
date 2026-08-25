<?php

declare(strict_types=1);

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'MapProvider',
    description: 'What type of map provider (open-free-map, open-railway-map) did the user specify?',
    type: 'string',
    example: 'open-free-map',
    enum: ['open-free-map', 'open-railway-map'],
)]
enum MapProvider: string
{
    case OPEN_FREE_MAP = 'open-free-map';
    case OPEN_RAILWAY_MAP = 'open-railway-map';
}
