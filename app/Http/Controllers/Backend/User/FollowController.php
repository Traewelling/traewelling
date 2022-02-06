<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Collection;

abstract class FollowController extends Controller
{
    public static function getFollowers(User $user): Collection {
        return $user->followers()->with(relations: 'user')->simplePaginate(perPage: 15)->pluck('user');
    }

    public static function getFollowRequests(User $user): Collection {
        return $user->followRequests()->with('user')->simplePaginate(perPage: 15)->pluck('user');
    }

    public static function getFollowings(User $user) {
        return $user->userFollowings()->simplePaginate(perPage: 15);
    }
}
