<?php

namespace App\Http\Controllers\Backend\User;

use App\Exceptions\AlreadyFollowingException;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BlockController extends Controller
{
    public static function isBlocked(User $user, User $userToCheck): bool {
        return $user->blockedUsers->contains('id', $userToCheck->id);
    }
}
