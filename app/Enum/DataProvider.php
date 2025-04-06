<?php
declare(strict_types=1);

namespace App\Enum;

/**
 * @OA\Schema(
 *     title="DataProvider",
 *     description="What type of data provider did the user specify? (users need to be in closed-beta for this to take effect)",
 *     type="string",
 *     enum={"default","transitous"},
 *     example="cargo",
 * )
 */
enum DataProvider: string
{
    case DEFAULT = 'default';
    case TRANSITOUS = 'transitous';
}
