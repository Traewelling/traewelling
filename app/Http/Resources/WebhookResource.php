<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'WebhookResource',
    description: 'Webhook model',
    required: ['id', 'clientId', 'client', 'userId', 'user', 'url', 'createdAt', 'events', 'disabledAt'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'ID',
            format: 'int',
            example: 12345
        ),
        new OA\Property(
            property: 'client',
            ref: '#/components/schemas/ClientResource',
            description: 'Client which created this webhook'
        ),
        new OA\Property(
            property: 'clientId',
            description: 'Deprecated. Use client.id instead.',
            format: 'int',
            example: 12345,
            deprecated: true,
        ),
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/LightUserResource',
            description: 'User who created this webhook'
        ),
        new OA\Property(
            property: 'userId',
            description: 'Deprecated. Use user.id instead.',
            format: 'int',
            example: 12345,
            deprecated: true,
        ),
        new OA\Property(
            property: 'url',
            description: 'URL where the webhook gets sent to',
            example: 'https://example.com/webhook'
        ),
        new OA\Property(
            property: 'createdAt',
            description: 'The ISO 8601 timestamp when the webhook was created',
            type: 'string',
            format: 'date-time',
            example: '2024-01-01T12:00:00Z'
        ),
        new OA\Property(
            property: 'events',
            description: 'List of events which are triggered for this webhook',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/WebhookEventResource')
        ),
        new OA\Property(
            property: 'disabledAt',
            description: 'ISO 8601 timestamp when the webhook was automatically disabled due to repeated failures, or null if active',
            type: 'string',
            format: 'date-time',
            example: '2026-04-08T10:00:00Z',
            nullable: true
        ),
    ],
    type: 'object'
)]
class WebhookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'clientId' => $this->oauth_client_id, // @deprecated - remove after 2026-09-30
            'client' => new ClientResource($this->client),
            'userId' => $this->user_id, // @deprecated - remove after 2026-09-30
            'user' => new LightUserResource($this->user),
            'url' => $this->url,
            'createdAt' => $this->created_at->toIso8601String(),
            'events' => WebhookEventResource::collection($this->events),
            'disabledAt' => $this->disabled_at?->toIso8601String(),
        ];
    }
}
