<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Webhook',
    description: 'Webhook model',
    xml: new OA\Xml(name: 'Webhook'),
)]
class Webhook
{
    #[OA\Property(
        title: 'ID',
        description: 'ID',
        type: 'integer',
        format: 'int',
        example: 12345,
    )]
    private int $id;

    #[OA\Property(
        title: 'ClientID',
        description: 'ID of the client which created this webhook',
        type: 'integer',
        format: 'int',
        example: 12345,
    )]
    private int $clientId;

    #[OA\Property(
        title: 'UserID',
        description: 'ID of the user which created this webhook',
        type: 'integer',
        format: 'int',
        example: 12345,
    )]
    private int $userId;

    #[OA\Property(
        title: 'url',
        description: 'URL where the webhook gets sent to',
        type: 'string',
        example: 'https://example.com/webhook',
    )]
    private string $url;

    #[OA\Property(
        title: 'createdAt',
        description: 'creation date of this webhook',
        type: 'string',
        format: 'datetime',
        example: '2022-07-17T13:37:00+02:00',
    )]
    private string $createdAt;

    #[OA\Property(
        title: 'events',
        description: 'array of events this webhook receives',
        type: 'array',
        items: new OA\Items(),
    )]
    private array $events;
}
