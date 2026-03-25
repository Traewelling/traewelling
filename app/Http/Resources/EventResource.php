<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Event',
    required: ['id', 'name', 'slug', 'hashtag', 'host', 'url', 'begin', 'end', 'station', 'isPride'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 39),
        new OA\Property(property: 'name', type: 'string', example: '9-Euro-Ticket'),
        new OA\Property(property: 'slug', type: 'string', example: '9_euro_ticket'),
        new OA\Property(property: 'hashtag', type: 'string', example: 'NeunEuroTicket', nullable: true),
        new OA\Property(property: 'host', type: 'string', example: '9-Euro-Ticket GmbH', nullable: true),
        new OA\Property(property: 'url', type: 'string', example: 'https://9-euro-ticket.de', nullable: true),
        new OA\Property(property: 'begin', type: 'string', format: 'date', example: '2022-01-01'),
        new OA\Property(property: 'end', type: 'string', format: 'date', example: '2022-01-02'),
        new OA\Property(property: 'isPride', type: 'boolean', example: true),
        new OA\Property(property: 'station', ref: StationResource::class, nullable: true),
    ],
)]
class EventResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Event $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'hashtag' => $this->hashtag,
            'host' => $this->host,
            'url' => $this->url,
            'begin' => ($this->event_start ?? $this->checkin_start)->toIso8601String(),
            'end' => ($this->event_end ?? $this->checkin_end)->toIso8601String(),
            'isPride' => (bool) $this->isPride,
            'station' => new StationResource($this->station),
        ];
    }
}
