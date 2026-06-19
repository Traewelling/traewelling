<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Status;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AdminStatusResource',
    required: ['id', 'body', 'visibility', 'business', 'moderationNotes', 'lockVisibility', 'hideBody', 'eventId', 'user', 'checkin', 'stopovers', 'createdAt', 'updatedAt', 'client', 'createdBy'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 12345),
        new OA\Property(property: 'body', type: 'string', nullable: true),
        new OA\Property(property: 'visibility', type: 'integer', example: 0),
        new OA\Property(property: 'business', type: 'integer', example: 0),
        new OA\Property(property: 'moderationNotes', type: 'string', nullable: true),
        new OA\Property(property: 'lockVisibility', type: 'boolean'),
        new OA\Property(property: 'hideBody', type: 'boolean'),
        new OA\Property(property: 'eventId', type: 'integer', nullable: true),
        new OA\Property(property: 'user', ref: LightUserResource::class),
        new OA\Property(property: 'checkin', ref: TransportResource::class, nullable: true),
        new OA\Property(
            property: 'stopovers',
            type: 'array',
            items: new OA\Items(ref: StopoverResource::class),
        ),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'client',
            ref: ClientResource::class,
            nullable: true,
        ),
        new OA\Property(property: 'createdBy', ref: LightUserResource::class, nullable: true),
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
            'moderationNotes' => $this->moderation_notes,
            'lockVisibility' => (bool) $this->lock_visibility,
            'hideBody' => (bool) $this->hide_body,
            'eventId' => $this->event_id,
            'user' => new LightUserResource($this->user),
            'checkin' => $this->checkin ? new TransportResource($this->checkin) : null,
            'stopovers' => $this->checkin?->trip ? StopoverResource::collection($this->checkin->trip->stopovers) : [],
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'client' => new ClientResource($this->whenLoaded('client')),
            'createdBy' => new LightUserResource($this->whenLoaded('createdByUser')),
        ];
    }
}
