<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Notification',
    title: 'Notification',
    description: 'Notification model',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'bb1ba9a5-9c2b-43c3-b8c9-2f70651fc51c'),
        new OA\Property(property: 'type', type: 'string', example: 'UserJoinedConnection'),
        new OA\Property(property: 'leadFormatted', type: 'string', example: '<b>@bob</b> is in your connection!'),
        new OA\Property(property: 'lead', type: 'string', example: '@bob is in your connection!'),
        new OA\Property(property: 'noticeFormatted', type: 'string', example: '@bob is on <b>S 81</b> from <b>Karlsruhe Hbf</b> to <b>Freudenstadt Hbf</b>.'),
        new OA\Property(property: 'notice', type: 'string', example: '@bob is on S 81 from Karlsruhe Hbf to Freudenstadt Hbf.'),
        new OA\Property(property: 'link', type: 'string', example: 'https://traewelling.de/status/123456'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
        new OA\Property(property: 'readAt', type: 'string', nullable: true, example: '2023-01-01T00:00:00+00:00'),
        new OA\Property(property: 'createdAt', type: 'string', example: '2023-01-01T00:00:00+00:00'),
        new OA\Property(property: 'createdAtForHumans', type: 'string', example: '2 days ago'),
    ],
)]
class UserNotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'type' => (string) str_replace('App\\Notifications\\', '', $this->type),
            'leadFormatted' => $this->resource->type::getLead($this->resource->data),
            'lead' => strip_tags($this->resource->type::getLead($this->resource->data)),
            'noticeFormatted' => $this->resource->type::getNotice($this->resource->data),
            'notice' => strip_tags($this->resource->type::getNotice($this->resource->data)),
            'link' => $this->resource->type::getLink($this->resource->data),
            'data' => $this->data,
            'readAt' => $this->read_at?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
            'createdAtForHumans' => $this->created_at->diffForHumans(),
        ];
    }
}
