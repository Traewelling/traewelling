<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;

abstract class BlockController extends Controller
{
    public static function isBlocked(User $user, User $userToCheck): bool {
        return $user->blockedUsers->contains('id', $userToCheck->id);
    }
}
