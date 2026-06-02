<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\EventSuggestion;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'EventSuggestionResource',
    title: 'EventSuggestionResource',
    description: 'Event suggestion submitted by a user',
    required: ['id', 'name', 'host', 'url', 'hashtag', 'begin', 'end', 'station', 'user', 'processed', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Berliner Fahrradfest'),
        new OA\Property(property: 'host', type: 'string', nullable: true),
        new OA\Property(property: 'url', type: 'string', nullable: true),
        new OA\Property(property: 'hashtag', type: 'string', nullable: true),
        new OA\Property(property: 'begin', type: 'string', format: 'date', example: '2025-07-01'),
        new OA\Property(property: 'end', type: 'string', format: 'date', example: '2025-07-03'),
        new OA\Property(property: 'station', properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'name', type: 'string'),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'user', properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'username', type: 'string'),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'processed', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ],
)]
class EventSuggestionResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var EventSuggestion $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'host' => $this->host,
            'url' => $this->url,
            'hashtag' => $this->hashtag,
            'begin' => $this->begin->toDateString(),
            'end' => $this->end->toDateString(),
            'station' => $this->station ? ['id' => $this->station->id, 'name' => $this->station->name] : null,
            'user' => $request->user()?->hasRole('admin') && $this->user
                ? ['id' => $this->user->id, 'username' => $this->user->username]
                : null,
            'processed' => $this->processed,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
