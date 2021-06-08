<?php

namespace App\Http\Controllers\API\v1;


use App\Exceptions\PermissionException;
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
     * @param string $username
     * @return UserResource
     * @todo Maybe put this into another method?
     */
    public function show(string $username): UserResource {
        return new UserResource(User::where('username', 'like', $username)->firstOrFail());
    }

    /**
     * Returns paginated statuses for user
     * @param string $username
     * @return AnonymousResourceCollection
     */
    public static function statuses(string $username): AnonymousResourceCollection {
        $user = User::where('username', 'like', $username)->firstOrFail();
        try {
            $userResponse = UserBackend::statusesForUser($user);
        } catch (PermissionException) {
            abort(404, "No statuses found, or statuses are not visible to you.");
        }
        return StatusResource::collection($userResponse);
    }
}
