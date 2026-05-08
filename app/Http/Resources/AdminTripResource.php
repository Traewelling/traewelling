<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Trip;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AdminTrip',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'tripId', type: 'string'),
        new OA\Property(property: 'category', type: 'string'),
        new OA\Property(property: 'mode', type: 'string', nullable: true),
        new OA\Property(property: 'number', type: 'string', nullable: true),
        new OA\Property(property: 'lineName', type: 'string', nullable: true),
        new OA\Property(property: 'journeyNumber', type: 'integer', nullable: true),
        new OA\Property(property: 'operator', type: 'string', nullable: true),
        new OA\Property(property: 'source', type: 'string', nullable: true),
        new OA\Property(property: 'user', ref: LightUserResource::class, nullable: true),
        new OA\Property(property: 'lastRefreshed', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'stopovers',
            type: 'array',
            items: new OA\Items(ref: AdminStopoverResource::class),
        ),
        new OA\Property(
            property: 'statuses',
            type: 'array',
            items: new OA\Items(ref: AdminStatusResource::class),
        ),
    ],
)]
class AdminTripResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Trip $this */
        return [
            'id' => $this->id,
            'tripId' => $this->trip_id,
            'category' => $this->category->value,
            'mode' => $this->mode?->value,
            'number' => $this->number,
            'lineName' => $this->linename,
            'journeyNumber' => $this->journey_number,
            'operator' => $this->operator?->name,
            'source' => $this->source?->name,
            'user' => $this->whenLoaded('user', fn () => $this->user ? new LightUserResource($this->user) : null),
            'lastRefreshed' => $this->last_refreshed?->toIso8601String(),
            'stopovers' => AdminStopoverResource::collection($this->whenLoaded('stopovers')),
            'statuses' => $this->whenLoaded('checkins', fn () => AdminStatusResource::collection(
                $this->checkins->map(fn ($checkin) => $checkin->status)->filter()
            )),
        ];
    }
}
