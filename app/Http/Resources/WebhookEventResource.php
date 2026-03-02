<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'WebhookEventResource',
    description: 'WebhookEvent model',
    required: ['type'],
    type: 'object',
    properties: [
        new OA\Property(
            property: 'type',
            description: 'The type of the event',
            example: 'notification'
        ),
    ]
)]
class WebhookEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'type' => $this->event->value,
        ];
    }
}
