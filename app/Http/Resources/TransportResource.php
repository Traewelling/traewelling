<?php

namespace App\Http\Resources;

use App\Models\Checkin;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="TransportResource",
 *     @OA\Property(property="trip", type="integer", example="4711"),
 *     @OA\Property(property="hafasId", type="string", example="1|1234|567"),
 *     @OA\Property(property="category", ref="#/components/schemas/HafasTravelType"),
 *     @OA\Property(property="number", description="Internal number of the journey", example="4-a6s8-8"),
 *     @OA\Property(property="lineName", type="string", example="S 1"),
 *     @OA\Property(property="journeyNumber", type="integer", example=85639),
 *     @OA\Property(property="distance", type="integer", description="Distance in meters", example=10000),
 *     @OA\Property(property="points", type="integer", example=37),
 *     @OA\Property(property="duration", type="integer", description="Duration in minutes", example=30),
 *     @OA\Property(property="manualDeparture", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true),
 *     @OA\Property(property="manualArrival", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true),
 *     @OA\Property(property="origin", ref="#/components/schemas/StopoverResource"),
 *     @OA\Property(property="destination", ref="#/components/schemas/StopoverResource"),
 *     @OA\Property(property="operator", ref="#/components/schemas/OperatorResource")
 * )
 */
class TransportResource extends JsonResource
{
    public function toArray($request): array {
        $pointsEnabled = $request->user()?->points_enabled ?? true;
        /** @var Checkin $this */
        return [
            'trip'            => (int) $this->trip->id,
            'hafasId'         => (string) $this->trip->trip_id,
            'category'        => (string) $this->trip->category->value,
            'number'          => (string) $this->trip->number,
            'lineName'        => (string) $this->trip->linename,
            'journeyNumber'   => $this->trip->journey_number,
            'distance'        => (int) $this->distance,
            'points'          => (int) $pointsEnabled ? $this->points : 0,
            'duration'        => (int) $this->duration,
            'manualDeparture' => $this->manual_departure?->toIso8601String(),
            'manualArrival'   => $this->manual_arrival?->toIso8601String(),
            'origin'          => new StopoverResource($this->originStopover),
            'destination'     => new StopoverResource($this->destinationStopover),
            'operator'        => new OperatorResource($this?->trip->operator)
        ];
    }
}
