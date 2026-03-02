<?php

declare(strict_types=1);

namespace App\Enum\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'FriendCheckinSetting',
    type: 'string',
    example: 'forbidden',
    enum: ['forbidden', 'friends', 'list'],
)]
enum FriendCheckinSetting: string
{
    case FORBIDDEN = 'forbidden'; // default
    case FRIENDS = 'friends';   // user who are following each other
    case LIST = 'list';      // specific list of users
}
