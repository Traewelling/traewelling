<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\UserController as UserBackend;

class UserController extends Controller
{
    /**
     * Returns Model of user
     * @param $username
     * @return UserResource
     */
    public function show($username): UserResource {
        // ToDo: Maybe put this into another method?
        return new UserResource(User::where('username', 'like', $username)->firstOrFail());
    }

    /**
     * Returns paginated statuses for user
     * @param $username
     * @return AnonymousResourceCollection
     */
    public static function statuses($username): AnonymousResourceCollection {
        $user         = User::where('username', 'like', $username)->firstOrFail();
        $userResponse = UserBackend::statusesForUser($user);
        if (!$userResponse) {
            abort(404, "No statuses found, or statuses are not visible to you.");
        }
        return StatusResource::collection($userResponse);
    }
}
