<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Status;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AdminStatusResource',
    required: ['id', 'body', 'visibility', 'business', 'moderation_notes', 'lock_visibility', 'hide_body', 'event_id', 'user', 'checkin', 'stopovers', 'created_at', 'updated_at', 'client', 'created_by'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 12345),
        new OA\Property(property: 'body', type: 'string', nullable: true),
        new OA\Property(property: 'visibility', type: 'integer', example: 0),
        new OA\Property(property: 'business', type: 'integer', example: 0),
        new OA\Property(property: 'moderation_notes', type: 'string', nullable: true),
        new OA\Property(property: 'lock_visibility', type: 'boolean'),
        new OA\Property(property: 'hide_body', type: 'boolean'),
        new OA\Property(property: 'event_id', type: 'integer', nullable: true),
        new OA\Property(
            property: 'user',
            required: ['id', 'name', 'username'],
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'username', type: 'string'),
            ],
            type: 'object',
        ),
        new OA\Property(
            property: 'checkin',
            required: ['id', 'origin_station_id', 'origin_station_name', 'destination_station_id', 'destination_station_name', 'departure', 'arrival', 'distance', 'points', 'trip_id', 'linename'],
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'origin_station_id', type: 'integer', nullable: true),
                new OA\Property(property: 'origin_station_name', type: 'string', nullable: true),
                new OA\Property(property: 'destination_station_id', type: 'integer', nullable: true),
                new OA\Property(property: 'destination_station_name', type: 'string', nullable: true),
                new OA\Property(property: 'departure', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'arrival', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'distance', type: 'integer'),
                new OA\Property(property: 'points', type: 'integer'),
                new OA\Property(property: 'trip_id', type: 'integer'),
                new OA\Property(property: 'linename', type: 'string', nullable: true),
            ],
            type: 'object',
            nullable: true,
        ),
        new OA\Property(
            property: 'stopovers',
            type: 'array',
            items: new OA\Items(
                required: ['station_id', 'station_name', 'arrival_planned', 'departure_planned'],
                properties: [
                    new OA\Property(property: 'station_id', type: 'integer'),
                    new OA\Property(property: 'station_name', type: 'string'),
                    new OA\Property(property: 'arrival_planned', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'departure_planned', type: 'string', format: 'date-time', nullable: true),
                ],
            ),
            nullable: true,
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'client',
            required: ['id', 'name', 'username'],
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string', nullable: true),
            ],
            nullable: true,
            type: 'object',
        ),
        new OA\Property(
            property: 'created_by',
            required: ['id', 'name', 'username'],
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string', nullable: true),
                new OA\Property(property: 'username', type: 'string', nullable: true),
            ],
            nullable: true,
            type: 'object',
        ),
    ],
)]
class AdminStatusResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Status $this */
        return [
            'id' => (int) $this->id,
            'body' => $this->body,
            'visibility' => (int) $this->visibility->value,
            'business' => (int) $this->business->value,
            'moderation_notes' => $this->moderation_notes,
            'lock_visibility' => (bool) $this->lock_visibility,
            'hide_body' => (bool) $this->hide_body,
            'event_id' => $this->event_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
            ],
            'checkin' => $this->checkin ? [
                'id' => $this->checkin->id,
                'origin_station_id' => $this->checkin->originStopover?->train_station_id,
                'origin_station_name' => $this->checkin->originStopover?->station?->name,
                'destination_station_id' => $this->checkin->destinationStopover?->train_station_id,
                'destination_station_name' => $this->checkin->destinationStopover?->station?->name,
                'departure' => $this->checkin->departure?->toIso8601String(),
                'arrival' => $this->checkin->arrival?->toIso8601String(),
                'distance' => (int) $this->checkin->distance,
                'points' => (int) $this->checkin->points,
                'trip_id' => $this->checkin->trip?->id,
                'linename' => $this->checkin->trip?->linename,
            ] : null,
            'stopovers' => $this->when(
                $this->checkin?->trip?->relationLoaded('stopovers') ?? false,
                fn () => $this->checkin->trip->stopovers->map(fn ($s) => [
                    'station_id' => (int) $s->train_station_id,
                    'station_name' => $s->station?->name,
                    'arrival_planned' => $s->arrival_planned?->toIso8601String(),
                    'departure_planned' => $s->departure_planned?->toIso8601String(),
                ])->values(),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'client' => $this->client ? [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ] : null,
            'created_by' => $this->createdByUser ? [
                'id' => $this->createdByUser->id,
                'name' => $this->createdByUser->name,
                'username' => $this->createdByUser->username,
            ] : null,
        ];
    }
}
