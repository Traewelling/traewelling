<?php

namespace App\Http\Resources;

use App\Enum\StatusTagKey;
use App\Models\Checkin;
use App\Models\StatusTag;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="TransportResource",
 *     required={"trip", "hafasId", "category", "number", "lineName", "journeyNumber", "distance", "points", "duration",
 *      "manualDeparture", "manualArrival", "origin", "destination", "operator", "dataSource"},
 *     @OA\Property(property="trip", type="integer", example="4711"),
 *     @OA\Property(property="hafasId", type="string", example="1|1234|567"),
 *     @OA\Property(property="category", ref="#/components/schemas/HafasTravelType"),
 *     @OA\Property(property="number", description="Internal number of the journey", example="4-a6s8-8"),
 *     @OA\Property(property="lineName", type="string", example="S 1"),
 *     @OA\Property(property="routeColor", type="string", example="FFEE00", nullable=true, description="Hex color code of the route, if available"),
 *     @OA\Property(property="journeyNumber", type="integer", example=85639),
 *     @OA\Property(property="manualJourneyNumber", type="string", example="ICE 4", nullable=true,
 *     description="Manual journey number, if set by the user. This is intended for use cases like ICE lines in germany that have line number but are more widely known by their train number"),
 *     @OA\Property(property="distance", type="integer", description="Distance in meters", example=10000),
 *     @OA\Property(property="points", type="integer", example=37),
 *     @OA\Property(property="duration", type="integer", description="Duration in minutes", example=30),
 *     @OA\Property(property="manualDeparture", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true),
 *     @OA\Property(property="manualArrival", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true),
 *     @OA\Property(property="origin", ref="#/components/schemas/StopoverResource"),
 *     @OA\Property(property="destination", ref="#/components/schemas/StopoverResource"),
 *     @OA\Property(property="operator", ref="#/components/schemas/OperatorResource", nullable=true),
 *     @OA\Property(property="dataSource", ref="#/components/schemas/DataSourceResource", nullable=true),
 * )
 */
class TransportResource extends JsonResource
{
    public function toArray($request): array {
        /** @var Checkin $this */
        $pointsEnabled       = $request->user()?->points_enabled ?? true;
        $manualJourneyNumber = StatusTag::whereStatusId($this->status_id)
                                        ->whereRaw('`key` = ?', [StatusTagKey::JOURNEY_NUMBER->value])
                                        ->first();
        return [
            'trip'                => (int) $this->trip->id,
            'hafasId'             => (string) $this->trip->trip_id,
            'category'            => (string) $this->trip->category->value,
            'number'              => (string) $this->trip->number,
            'lineName'            => (string) $this->trip->linename,
            'routeColor'          => $this->trip->route_color,
            'journeyNumber'       => $this->trip->journey_number,
            'manualJourneyNumber' => $manualJourneyNumber ? $manualJourneyNumber->value : null,
            'distance'            => (int) $this->distance,
            'points'              => (int) $pointsEnabled ? $this->points : 0,
            'duration'            => (int) $this->duration,
            'manualDeparture'     => $this->manual_departure?->toIso8601String(),
            'manualArrival'       => $this->manual_arrival?->toIso8601String(),
            'origin'              => new StopoverResource($this->originStopover),
            'destination'         => new StopoverResource($this->destinationStopover),
            'operator'            => $this?->trip->operator ? new OperatorResource($this?->trip->operator) : null,
            'dataSource'          => $this->trip->motisSourceLicense ? new DataSourceResource($this->trip->motisSourceLicense) : null,
        ];
    }
}
