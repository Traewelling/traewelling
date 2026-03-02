<?php

namespace App\Http\Resources;

use App\Dto\MentionDto;
use App\Models\Status;
use App\Models\StatusTag;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

/**
 * @todo: add moderation_notes, lock_visibility and hide_body
 */
#[OA\Schema(
    title: 'Status',
    required: [
        'id',
        'body',
        'business',
        'bodyMentions',
        'business',
        'visibility',
        'likes',
        'liked',
        'isLikable',
        'client',
        'createdAt',
        'train',
        'event',
        'userDetails',
        'tags',
        'checkin',
        'user',
        'createdBy',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 12345),
        new OA\Property(
            property: 'body',
            description: 'User defined status text',
            example: 'Hello world!',
        ),
        new OA\Property(
            property: 'bodyMentions',
            description: 'Mentions in the status body',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/MentionDto'),
        ),
        new OA\Property(property: 'business', ref: '#/components/schemas/Business'),
        new OA\Property(property: 'visibility', ref: '#/components/schemas/StatusVisibility'),
        new OA\Property(
            property: 'likes',
            description: 'How many people have liked this status',
            type: 'integer',
            example: 12,
        ),
        new OA\Property(
            property: 'liked',
            description: 'Did the currently authenticated user like this status? (if unauthenticated = false)',
            type: 'boolean',
            example: true,
        ),
        new OA\Property(
            property: 'isLikable',
            description: 'Do the author of this status and the currently authenticated user allow liking of statuses? Only show the like UI if set to true',
            type: 'boolean',
            example: true,
        ),
        new OA\Property(property: 'client', ref: '#/components/schemas/ClientResource'),
        new OA\Property(property: 'checkin', ref: '#/components/schemas/TransportResource'),
        new OA\Property(
            property: 'event',
            ref: '#/components/schemas/EventResource',
            nullable: true,
        ),
        new OA\Property(property: 'user', ref: '#/components/schemas/LightUserResource'),
        new OA\Property(
            property: 'createdBy',
            description: 'User who created this check-in on behalf of the status owner (null if self-checkin)',
            ref: '#/components/schemas/LightUserResource',
            nullable: true,
        ),
        new OA\Property(
            property: 'tags',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/StatusTagResource'),
        ),
        new OA\Property(
            property: 'createdAt',
            description: 'creation date of this status',
            type: 'string',
            format: 'datetime',
            example: '2022-07-17T13:37:00+02:00',
        ),
    ],
)]
class StatusResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Status $this */
        return [
            'id' => (int) $this->id,
            'body' => (string) $this->body,
            'bodyMentions' => $this->mentions->map(
                fn ($mention) => new MentionDto($mention->mentioned, $mention->position, $mention->length)
            ),
            'business' => (int) $this->business->value,
            'visibility' => (int) $this->visibility->value,
            'likes' => (int) $this->likes->count(),
            'liked' => (bool) $this->favorited,
            'isLikable' => Gate::allows('like', $this->resource),
            'checkin' => new TransportResource($this->checkin),
            'client' => new ClientResource($this->client),
            'event' => new EventResource($this?->event),
            'user' => new LightUserResource($this->user),
            'createdBy' => $this->createdByUser ? new LightUserResource($this->createdByUser) : null,
            'tags' => StatusTagResource::collection($this->tags->filter(fn (StatusTag $tag) => Gate::allows('view', $tag))),
            'createdAt' => $this->created_at->toIso8601String(),

            'train' => new TransportResource($this->checkin), // TODO: delete after 2026-07 (replaced by 'checkin')
            'userDetails' => new LightUserResource($this->user), // TODO: delete after 2026-07 (replaced by 'user')
        ];
    }
}
