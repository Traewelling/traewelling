<?php

declare(strict_types=1);

namespace App\Virtual\Models\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'CheckinForbiddenWithUsersResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'You are not allowed to check in the following users: 1'),
        new OA\Property(
            property: 'meta',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'invalidUsers',
                    type: 'array',
                    items: new OA\Items(type: 'integer', example: '1'),
                ),
            ],
        ),
    ],
)]
class CheckinForbiddenWithUsersResponse {}
