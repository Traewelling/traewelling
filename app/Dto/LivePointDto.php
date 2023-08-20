<?php

namespace App\Dto;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="LivePointDto",
 *     description="All necessary information to calculate live position",
 *     @OA\Xml(
 *         name="LivePointDto"
 *     )
 * )
 */
class LivePointDto implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     title="point",
     *     description="current point, if stopping at a station",
     *     nullable="true",
     *     ref="#/components/schemas/Coordinate"
     * )
     */
    public readonly ?Coordinate $point;
    /**
     * @OA\Property(
     *     title="polyline",
     *     description="geojson point collection of the next line segment",
     *     ref="#/components/schemas/FeatureCollection"
     * )
     */
    public readonly ?\stdClass  $polyline;
    /**
     * @OA\Property(
     *     title="arrival",
     *     description="arrival at end of polyline in UNIX time format",
     *     format="integer",
     *     example=1692538680
     * )
     */
    public readonly int         $arrival;
    /**
     * @OA\Property(
     *     title="departure",
     *     description="departure at start of polyline in UNIX time format",
     *     format="integer",
     *     example=1692538740
     * )
     */
    public readonly int         $departure;
    /**
     * @OA\Property(
     *     title="lineName",
     *     description="name of line",
     *     format="string",
     *     example="ICE 123"
     * )
     **/
    public readonly string      $lineName;
    /**
     * @OA\Property(
     *     title="statusId",
     *     description="ID of status",
     *     format="int64",
     *     example=12345
     * )
     **/
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
        return $this->toArray();
    }

    public function toArray(): array {
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
