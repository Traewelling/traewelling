<?php
declare(strict_types=1);

namespace App\Enum;

/**
 * @OA\Schema(
 *     title="MapProvider",
 *     description="What type of map provider (cargo, open-railway-map) did the user specify?",
 *     type="string",
 *     enum={"cargo","open-railway-map"},
 *     example="cargo",
 * )
 */
enum MapProvider: string
{
    case CARGO            = 'cargo';
    case OPEN_RAILWAY_MAP = 'open-railway-map';
}
