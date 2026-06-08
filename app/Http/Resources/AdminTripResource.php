<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Trip;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AdminTrip',
    required: ['id', 'tripId', 'checkinsCount', 'category', 'mode', 'number', 'lineName', 'routeColor', 'journeyNumber',
        'operator', 'source', 'user', 'lastRefreshed', 'origin', 'destination', 'stopovers', 'statuses'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'tripId', type: 'string'),
        new OA\Property(property: 'checkinsCount', type: 'integer', nullable: true),
        new OA\Property(property: 'category', type: 'string'),
        new OA\Property(property: 'mode', type: 'string', nullable: true),
        new OA\Property(property: 'number', type: 'string', nullable: true),
        new OA\Property(property: 'lineName', type: 'string', nullable: true),
        new OA\Property(property: 'routeColor', type: 'string', nullable: true),
        new OA\Property(property: 'journeyNumber', type: 'integer', nullable: true),
        new OA\Property(property: 'operator', type: 'string', nullable: true),
        new OA\Property(property: 'source', type: 'string', nullable: true),
        new OA\Property(property: 'user', ref: LightUserResource::class, nullable: true),
        new OA\Property(property: 'lastRefreshed', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'origin',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
            ],
        ),
        new OA\Property(
            property: 'destination',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
            ],
        ),
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
            'checkinsCount' => $this->checkins_count,
            'category' => $this->category->value,
            'mode' => $this->mode?->value,
            'number' => $this->number,
            'lineName' => $this->linename,
            'routeColor' => $this->route_color,
            'journeyNumber' => $this->journey_number,
            'operator' => $this->operator?->name,
            'source' => $this->source?->name,
            'user' => $this->whenLoaded('user', fn () => $this->user ? new LightUserResource($this->user) : null),
            'lastRefreshed' => $this->last_refreshed?->toIso8601String(),
            'origin' => $this->whenLoaded('originStation', fn () => $this->originStation ? [
                'id' => (int) $this->origin_id,
                'name' => $this->originStation->name,
            ] : null),
            'destination' => $this->whenLoaded('destinationStation', fn () => $this->destinationStation ? [
                'id' => (int) $this->destination_id,
                'name' => $this->destinationStation->name,
            ] : null),
            'stopovers' => AdminStopoverResource::collection($this->whenLoaded('stopovers')),
            'statuses' => $this->whenLoaded('checkins', fn () => AdminStatusResource::collection(
                $this->checkins->map(fn ($checkin) => $checkin->status)->filter()
            )),
        ];
    }
}
