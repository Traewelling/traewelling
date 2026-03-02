<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Event',
    required: ['id', 'name', 'slug', 'hashtag', 'host', 'url', 'begin', 'end', 'station', 'isPride'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 39),
        new OA\Property(property: 'name', type: 'string', example: '9-Euro-Ticket'),
        new OA\Property(property: 'slug', type: 'string', example: '9_euro_ticket'),
        new OA\Property(property: 'hashtag', type: 'string', example: 'NeunEuroTicket'),
        new OA\Property(property: 'host', type: 'string', example: '9-Euro-Ticket GmbH'),
        new OA\Property(property: 'url', type: 'string', example: 'https://9-euro-ticket.de'),
        new OA\Property(property: 'begin', type: 'string', format: 'date', example: '2022-01-01'),
        new OA\Property(property: 'end', type: 'string', format: 'date', example: '2022-01-02'),
        new OA\Property(property: 'station', type: 'string', ref: '#/components/schemas/Station'),
        new OA\Property(property: 'isPride', ref: '#/components/schemas/StationResource'),
    ],
)]
class EventResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\Event $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'hashtag' => $this->hashtag,
            'host' => $this->host,
            'url' => $this->url,
            'begin' => ($this->event_start ?? $this->checkin_start)->toIso8601String(),
            'end' => ($this->event_end ?? $this->checkin_end)->toIso8601String(),
            'station' => new StationResource($this->station),
            'isPride' => $this->isPride ? true : false,
        ];
    }
}
