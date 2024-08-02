<?php
declare(strict_types=1);

namespace App\Enum\User;

/**
 * @OA\Schema(
 *     title="FriendCheckinSetting",
 *     type="string",
 *     enum={"forbidden", "friends", "list"},
 *     example="forbidden",
 * )
 */
enum FriendCheckinSetting: string
{
    case FORBIDDEN = 'forbidden'; // default
    case FRIENDS   = 'friends';   // user who are following each other
    case LIST      = 'list';      // specific list of users
}

