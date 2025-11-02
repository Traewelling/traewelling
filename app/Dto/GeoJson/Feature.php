<?php

namespace App\Dto\GeoJson;

use App\Dto\Coordinate;

class Feature implements \JsonSerializable
{
    /**
     * @var Coordinate[] $coordinates
     */
    private array      $coordinates;
    private string     $type;
    private ?int       $statusId;
    private Properties $properties;

    public function __construct(array $coordinates, string $type = 'LineString', ?int $statusId = null, array $properties = []) {
        $this->coordinates = $coordinates;
        $this->type        = $type;
        $this->statusId    = $statusId;
        $this->properties  = new Properties($properties);
    }

    public function setStationId(int $stationId): void {
        $this->properties->addProperty('stationId', $stationId);
    }

    public function setDeparturePlanned(?string $departurePlannedAt): void {
        if (!$departurePlannedAt) {
            return;
        }
        $this->properties->addProperty('departurePlanned', $departurePlannedAt);
    }

    public function setArrivalPlanned(?string $arrivalPlannedAt): void {
        if (!$arrivalPlannedAt) {
            return;
        }
        $this->properties->addProperty('arrivalPlanned', $arrivalPlannedAt);
    }

    public static function fromCoordinate(Coordinate $coordinate): self {
        return new self([$coordinate->longitude, $coordinate->latitude], 'Point');
    }

    public function getCoordinates(bool $invert = false): array {
        if (!$invert) {
            return $this->coordinates;
        }
        return $this->getCoordinateArray(true);
    }

    public function getCoordinateArray(bool $invert = false): array {
        if ($invert) {
            return array_map(
                function($coordinate) {
                    return [$coordinate->latitude, $coordinate->longitude];
                },
                $this->coordinates
            );
        }

        return array_map(
            function($coordinate) {
                return [$coordinate->longitude, $coordinate->latitude];
            },
            $this->coordinates
        );
    }

    public function toArray(): array {
        if ($this->statusId) {
            $this->properties->addProperty('statusId', $this->statusId);
        }

        $properties = $this->properties->toArray();
        if (empty($properties)) {
            $properties = new \stdClass();
        }

        return [
            'type'       => 'Feature',
            'geometry'   => [
                'type'        => $this->type,
                'coordinates' => $this->coordinates
            ],
            'properties' => $properties,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
