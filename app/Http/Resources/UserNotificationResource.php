<?php

namespace App\Http\Resources;

use App\Notifications\BaseNotification;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Notification',
    title: 'Notification',
    description: 'Notification model',
    required: ['id', 'type', 'leadFormatted', 'lead', 'notice', 'noticeFormatted', 'link', 'data', 'readAt', 'createdAt', 'createdAtForHumans'],
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'bb1ba9a5-9c2b-43c3-b8c9-2f70651fc51c'),
        new OA\Property(property: 'type', type: 'string', example: 'UserJoinedConnection'),
        new OA\Property(property: 'leadFormatted', type: 'string', example: '<b>@bob</b> is in your connection!'),
        new OA\Property(property: 'lead', type: 'string', example: '@bob is in your connection!'),
        new OA\Property(property: 'noticeFormatted', type: 'string', example: '@bob is on <b>S 81</b> from <b>Karlsruhe Hbf</b> to <b>Freudenstadt Hbf</b>.'),
        new OA\Property(property: 'notice', type: 'string', example: '@bob is on S 81 from Karlsruhe Hbf to Freudenstadt Hbf.'),
        new OA\Property(property: 'link', type: 'string', example: 'https://traewelling.de/status/123456'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
        new OA\Property(property: 'readAt', type: 'string', example: '2023-01-01T00:00:00+00:00', nullable: true),
        new OA\Property(property: 'createdAt', type: 'string', example: '2023-01-01T00:00:00+00:00'),
        new OA\Property(property: 'createdAtForHumans', type: 'string', example: '2 days ago'),
    ],
)]
class UserNotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var DatabaseNotification $this */
        /** @var BaseNotification $type */
        $type = $this->resource->type;

        return [
            'id' => (string) $this->id,
            'type' => (string) str_replace('App\\Notifications\\', '', $this->type),
            'leadFormatted' => $type::getLead($this->resource->data, $this->notifiable()?->first()?->language ?? null),
            'lead' => self::toPlainText($type::getLead($this->resource->data, $this->notifiable()?->first()?->language ?? null)),
            'noticeFormatted' => $type::getNotice($this->resource->data, $this->notifiable()?->first()?->language ?? null),
            'notice' => self::toPlainText($type::getNotice($this->resource->data, $this->notifiable()?->first()?->language ?? null)),
            'link' => $type::getLink($this->resource->data),
            'data' => $this->data,
            'readAt' => $this->read_at?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
            'createdAtForHumans' => $this->created_at->diffForHumans(),
        ];
    }

    /**
     * The formatted variants are HTML, so their entities need to be decoded
     * again for the plain text fields.
     */
    private static function toPlainText(?string $formatted): ?string
    {
        if ($formatted === null) {
            return null;
        }

        return html_entity_decode(strip_tags($formatted), ENT_QUOTES | ENT_HTML5);
    }
}
