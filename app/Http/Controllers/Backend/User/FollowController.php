<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;

abstract class FollowController extends Controller
{
    public static function getFollowers(User $user): Paginator {
        return $user->followers()->with(relations: 'user')->simplePaginate(perPage: 15);
    }

    public static function getFollowRequests(User $user): Paginator {
        return $user->followRequests()->with('user')->simplePaginate(perPage: 15);
    }

    public static function getFollowings(User $user): Paginator {
        return $user->followings()->with('user')->simplePaginate(perPage: 15);
    }
}
