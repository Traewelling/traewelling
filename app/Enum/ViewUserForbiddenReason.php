<?php

declare(strict_types=1);

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'ViewUserForbiddenReason',
    type: 'string',
    enum: ['PRIVATE_PROFILE', 'USER_MUTED', 'YOU_ARE_BLOCKED', 'USER_BLOCKED'],
    example: 'PRIVATE_PROFILE',
)]
enum ViewUserForbiddenReason: string
{
    case PrivateProfile = 'PRIVATE_PROFILE';
    case Muted = 'USER_MUTED';
    case YoureBlocked = 'YOU_ARE_BLOCKED';
    case Blocked = 'USER_BLOCKED';
}
