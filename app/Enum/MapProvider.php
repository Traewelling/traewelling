<?php
declare(strict_types=1);

namespace App\Enum;

/**
 *
 */
enum MapProvider: string
{
    case CARGO            = 'cargo';
    case OPEN_RAILWAY_MAP = 'open-railway-map';
}
