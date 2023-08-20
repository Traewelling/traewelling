<?php
declare(strict_types=1);

namespace App\Dto;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Coordinate",
 *     description="GeoJson Coordinates",
 *     @OA\Xml(name="Coordinate"),
 * )
 */
class Coordinate
{
    /**
     *
     * @OA\Property(property="type", example="Feature"),
     * @OA\Property(property="properties", type="object", example="{}"),
     * @OA\Property(
     *     property="geometry",
     *     type="object",
     *     @OA\Property(property="type", type="string", example="Point"),
     *     @OA\Property(property="coordinates", type="array",
     *         @OA\Items(
     *             example="[8.39767,49.01625]"
     *         )
     *     )
     * )
     */
    public readonly float $latitude;
    public readonly float $longitude;

    public function __construct(float $latitude, float $longitude) {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    public static function fromGeoJson(\stdClass $point): ?self {
        if (isset($point->geometry->coordinates)) {
            return new self($point->geometry->coordinates[1], $point->geometry->coordinates[0]);
        }
        return null;
    }


}
