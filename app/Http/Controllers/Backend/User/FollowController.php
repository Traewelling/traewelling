<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;

abstract class FollowController extends Controller
{
    public static function getFollowers(User $user): Paginator {
        return $user->userFollowers()->simplePaginate(perPage: 15);
    }

    public static function getFollowRequests(User $user): Paginator {
        return $user->userFollowRequests()->simplePaginate(perPage: 15);
    }

    public static function getFollowings(User $user): Paginator {
        return $user->userFollowings()->simplePaginate(perPage: 15);
    }
}
