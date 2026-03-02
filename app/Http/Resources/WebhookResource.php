<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'WebhookResource',
    description: 'Webhook model',
    required: ['id', 'clientId', 'client', 'userId', 'url', 'createdAt', 'events'],
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            description: 'ID',
            format: 'int',
            example: 12345
        ),
        new OA\Property(
            property: 'client',
            description: 'Client which created this webhook',
            ref: '#/components/schemas/ClientResource'
        ),
        new OA\Property(
            property: 'clientId',
            description: 'ID of the client which created this webhook',
            format: 'int',
            example: 12345
        ),
        new OA\Property(
            property: 'userId',
            description: 'ID of the user which created this webhook',
            format: 'int',
            example: 12345
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
    ]
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
            'clientId' => $this->oauth_client_id, // TODO: should be removed
            'client' => new ClientResource($this->client),
            'userId' => $this->user_id, // TODO: should be removed and replaced with user object
            'url' => $this->url,
            'createdAt' => $this->created_at->toIso8601String(),
            'events' => WebhookEventResource::collection($this->events),
        ];
    }
}
