<?php

namespace App\Dto;

use App\Dto\GeoJson\Feature;
use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Models\Status;
use JsonSerializable;
use stdClass;

/**
 * @OA\Schema(
 *     title="LivePointDto",
 *     description="All necessary information to calculate live position",
 *     @OA\Xml(
 *         name="LivePointDto"
 *     )
 * )
 */
readonly class LivePointDto implements JsonSerializable
{
    /**
     * @OA\Property(
     *     title="point",
     *     description="current point, if stopping at a station",
     *     nullable="true",
     *     ref="#/components/schemas/Coordinate"
     * )
     */
    public ?Coordinate $point;
    /**
     * @OA\Property(
     *     title="polyline",
     *     description="geojson point collection of the next line segment",
     *     ref="#/components/schemas/FeatureCollection"
     * )
     */
    public ?stdClass $polyline;
    /**
     * @OA\Property(
     *     title="arrival",
     *     description="arrival at end of polyline in UNIX time format",
     *     format="integer",
     *     example=1692538680
     * )
     */
    public int $arrival;
    /**
     * @OA\Property(
     *     title="departure",
     *     description="departure at start of polyline in UNIX time format",
     *     format="integer",
     *     example=1692538740
     * )
     */
    public int $departure;
    /**
     * @OA\Property(
     *     title="lineName",
     *     description="name of line",
     *     format="string",
     *     example="ICE 123"
     * )
     **/
    public string $lineName;
    /**
     * @deprecated how to remove this unnecessary property (use status model instead) without breaking the API?
     *
     * @OA\Property(
     *     title="statusId",
     *     description="ID of status",
     *     format="int",
     *     example=12345
     * )
     **/
    public int $statusId;

    public Status $status;

    public function __construct(
        ?Coordinate $point,
        ?stdClass   $polyline,
        int         $arrival,
        int         $departure,
        string      $lineName,
        Status      $status
    ) {
        $this->point     = $point;
        $this->polyline  = $polyline;
        $this->arrival   = $arrival;
        $this->departure = $departure;
        $this->lineName  = $lineName;
        $this->status    = $status;
        $this->statusId  = $status->id;
    }

    public function jsonSerialize(): mixed {
        return $this->toArray();
    }

    public function toArray(): array {
        return [
            'point'     => $this->point ? Feature::fromCoordinate($this->point) : null,
            'polyline'  => $this->polyline,
            'arrival'   => $this->arrival,
            'departure' => $this->departure,
            'lineName'  => $this->lineName,
            'statusId'  => $this->status->id, //deprecate this... why aren't we using a resource here...? why is a DTO a resource now? isn't a DTO supposed to be a simple data transfer object for internal use?
            'status'    => [
                //undocumented
                'id'   => $this->status->id,
                'user' => [
                    'id'                => $this->status->user->id,
                    'username'          => $this->status->user->username,
                    'name'              => $this->status->user->name,
                    'profilePictureUrl' => ProfilePictureController::getUrl($this->status->user)
                ],
            ]
        ];
    }
}
