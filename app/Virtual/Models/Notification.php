<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Notification',
    description: 'Notification model',
    xml: new OA\Xml(name: 'Notification'),
)]
class Notification
{
    #[OA\Property(
        title: 'ID',
        description: 'ID',
        type: 'string',
        format: 'string',
        example: 'bb1ba9a5-9c2b-43c3-b8c9-2f70651fc51c',
    )]
    private string $id;

    #[OA\Property(
        title: 'type',
        description: 'type of notification',
        type: 'string',
        example: 'UserJoinedConnection',
    )]
    private string $type;

    #[OA\Property(
        title: 'leadFormatted',
        description: 'the title of notification in html formatted form',
        type: 'string',
        format: 'string',
        example: '<b>@bob</b> is in your connection!',
    )]
    private string $leadFormatted;

    #[OA\Property(
        title: 'lead',
        description: 'the title of notification in plain text form',
        type: 'string',
        format: 'string',
        example: '@bob is in your connection!',
    )]
    private string $lead;

    #[OA\Property(
        title: 'noticeFormatted',
        description: 'the body of notification in html formatted form',
        type: 'string',
        format: 'string',
        example: '@bob is on <b>S 81</b> from <b>Karlsruhe Hbf</b> to <b>Freudenstadt Hbf</b>.',
    )]
    private string $noticeFormatted;

    #[OA\Property(
        title: 'notice',
        description: 'the body of notification in plain text form',
        type: 'string',
        format: 'string',
        example: '@bob is on S 81 from Karlsruhe Hbf to Freudenstadt Hbf.',
    )]
    private string $notice;

    #[OA\Property(
        title: 'link',
        description: 'the link to the notification',
        type: 'string',
        format: 'string',
        example: 'https://traewelling.de/status/123456',
    )]
    private string $link;

    #[OA\Property(
        title: 'data',
        description: 'the data of the notification',
        type: 'array',
        items: new OA\Items(example: ['notice' => 'every notification type has different data attributes, just try it out']),
    )]
    private mixed $data;

    #[OA\Property(
        title: 'readAt',
        description: 'the date when the notification was read, null if not read yet',
        type: 'string',
        format: 'string',
        nullable: true,
        example: '2023-01-01T00:00:00+00:00',
    )]
    private string $readAt;

    #[OA\Property(
        title: 'createdAt',
        description: 'the date when the notification was created',
        type: 'string',
        format: 'string',
        example: '2023-01-01T00:00:00+00:00',
    )]
    private string $createdAt;

    #[OA\Property(
        title: 'createdAtForHumans',
        description: "DON'T USE THIS ATTRIBUTE! This Attribute will be removed in the future. The date when the notification was created, but in human readable form",
        type: 'string',
        format: 'string',
        example: '2 days ago',
    )]
    private string $createdAtForHumans;
}
