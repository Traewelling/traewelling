<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @deprecated Will be replaced by APIv1
 */
class UserController extends ResponseController
{
    public function show($username): JsonResponse {
        return $this->sendResponse(UserBackend::getProfilePage($username));
    }

    public function active($username) {
        //Somehow this breaks without a LIKE.
        $user           = User::where('username', 'LIKE', $username)->firstOrFail();
        $statusResponse = StatusBackend::getActiveStatuses($user->id, true);
        return $this->sendResponse($statusResponse);
    }

    public function avatar(string $username): JsonResponse {
        $user = User::where('username', $username)->first();
        if ($user === null) {
            return $this->sendResponse(null);
        }
        $profilePictureResponse = ProfilePictureController::generateProfilePicture($user);
        return $this->sendResponse($profilePictureResponse);
    }
}
