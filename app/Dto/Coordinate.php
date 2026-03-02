<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\GeoJson\Feature;
use JsonSerializable;
use OpenApi\Attributes as OA;
use stdClass;

#[OA\Schema(title: 'Coordinate', description: 'GeoJson Coordinates', xml: new OA\Xml(name: 'Coordinate'))]
readonly class Coordinate implements JsonSerializable
{
    #[OA\Property(property: 'type', example: 'Feature')]
    #[OA\Property(property: 'properties', type: 'object', example: '{}')]
    #[OA\Property(
        property: 'geometry',
        properties: [
            new OA\Property(property: 'type', type: 'string', example: 'Point'),
            new OA\Property(
                property: 'coordinates',
                type: 'array',
                items: new OA\Items(example: '[8.39767,49.01625]'),
            ),
        ],
        type: 'object',
    )]
    public float $latitude;

    public float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public static function fromGeoJson(stdClass|Feature $point): ?self
    {
        if (isset($point->geometry->coordinates) && is_array($point->geometry->coordinates) && count($point->geometry->coordinates) === 2) {
            return new self($point->geometry->coordinates[1], $point->geometry->coordinates[0]);
        }

        return null;
    }

    public function toArray(): array
    {
        return [$this->longitude, $this->latitude];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->latitude . ',' . $this->longitude;
    }
}
