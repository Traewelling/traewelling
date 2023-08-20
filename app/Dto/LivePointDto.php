<?php

namespace App\Dto;

class LivePointDto implements \JsonSerializable
{
    public readonly ?Coordinate $point;
    public readonly ?\stdClass  $polyline;
    public readonly int         $arrival;
    public readonly int         $departure;
    public readonly string      $lineName;
    public readonly int         $statusId;

    public function __construct(
        ?Coordinate $point,
        ?\stdClass  $polyline,
        int         $arrival,
        int         $departure,
        string      $lineName,
        int         $statusId
    ) {
        $this->point     = $point;
        $this->polyline  = $polyline;
        $this->arrival   = $arrival;
        $this->departure = $departure;
        $this->lineName  = $lineName;
        $this->statusId  = $statusId;
    }

    public function jsonSerialize(): mixed {
        return [
            'point'     => $this?->point?->toGeoJsonPoint(),
            'polyline'  => $this->polyline,
            'arrival'   => $this->arrival,
            'departure' => $this->departure,
            'lineName'  => $this->lineName,
            'statusId'  => $this->statusId
        ];
    }
}
