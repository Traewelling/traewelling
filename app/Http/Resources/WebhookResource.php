<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Webhook',
    title: 'Webhook',
    description: 'Webhook model',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 12345),
        new OA\Property(property: 'clientId', type: 'integer', example: 12345),
        new OA\Property(property: 'client', ref: '#/components/schemas/ClientResource'),
        new OA\Property(property: 'userId', type: 'integer', example: 12345),
        new OA\Property(property: 'url', type: 'string', example: 'https://example.com/webhook'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'datetime', example: '2022-07-17T13:37:00+02:00'),
        new OA\Property(
            property: 'events',
            description: 'array of events this webhook receives',
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'type', type: 'string', example: 'checkin'),
            ], type: 'object'),
        ),
    ],
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
