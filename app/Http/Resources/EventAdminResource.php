<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'EventAdminResource',
    title: 'EventAdminResource',
    description: 'Full event data for admin management',
    required: ['id', 'name', 'slug', 'hashtag', 'host', 'url', 'checkin_start', 'checkin_end', 'event_start', 'event_end', 'status', 'station'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Berlin Bahnhofsfest'),
        new OA\Property(property: 'slug', type: 'string', example: 'berlin_bahnhofsfest'),
        new OA\Property(property: 'hashtag', type: 'string', example: 'BahnhofsFest', nullable: true),
        new OA\Property(property: 'host', type: 'string', example: 'DB AG', nullable: true),
        new OA\Property(property: 'url', type: 'string', example: 'https://example.com', nullable: true),
        new OA\Property(property: 'checkin_start', type: 'string', format: 'date', example: '2025-06-01'),
        new OA\Property(property: 'checkin_end', type: 'string', format: 'date', example: '2025-06-30'),
        new OA\Property(property: 'event_start', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'event_end', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['future', 'current', 'past']),
        new OA\Property(property: 'station', ref: '#/components/schemas/Station', nullable: true),
    ],
)]
class EventAdminResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Event $this */
        $today = today()->toDateString();

        if ($this->checkin_start->toDateString() > $today) {
            $status = 'future';
        } elseif ($this->checkin_end->toDateString() < $today) {
            $status = 'past';
        } else {
            $status = 'current';
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'hashtag' => $this->hashtag,
            'host' => $this->host,
            'url' => $this->url,
            'checkin_start' => $this->checkin_start->toDateString(),
            'checkin_end' => $this->checkin_end->toDateString(),
            'event_start' => $this->event_start?->toDateString(),
            'event_end' => $this->event_end?->toDateString(),
            'status' => $status,
            'station' => $this->station ? ['id' => $this->station->id, 'name' => $this->station->name] : null,
        ];
    }
}
