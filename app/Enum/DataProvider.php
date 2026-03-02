<?php

declare(strict_types=1);

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'DataProvider',
    description: 'What type of data provider did the user specify? (users need to be in closed-beta for this to take effect)',
    type: 'string',
    example: 'cargo',
    enum: ['default', 'transitous'],
)]
enum DataProvider: string
{
    case DEFAULT = 'default';
    case TRANSITOUS = 'transitous';

    public function isMotis(): bool
    {
        return $this === self::TRANSITOUS;
    }
}
