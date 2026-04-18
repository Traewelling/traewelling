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
        new OA\Property(property: 'user', ref: LightUserResource::class),
        new OA\Property(property: 'checkin', ref: TransportResource::class, nullable: true),
        new OA\Property(
            property: 'stopovers',
            type: 'array',
            items: new OA\Items(ref: StopoverResource::class),
            nullable: true,
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'client',
            ref: ClientResource::class,
            nullable: true,
        ),
        new OA\Property(property: 'created_by', ref: LightUserResource::class, nullable: true),
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
            'user' => new LightUserResource($this->user),
            'checkin' => $this->checkin ? new TransportResource($this->checkin) : null,
            'stopovers' => $this->checkin?->trip ? StopoverResource::collection($this->checkin->trip->stopovers) : [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'client' => new ClientResource($this->whenLoaded('client')),
            'created_by' => new LightUserResource($this->whenLoaded('createdByUser')),
        ];
    }
}
