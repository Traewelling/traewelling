<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ResponseController
{
    public function show($username) {
        $userResponse = UserBackend::getProfilePage($username);
        return $this->sendResponse($userResponse);
    }

    public function active($username) {
        //Somehow this breaks without a LIKE.
        $user           = User::where('username', 'LIKE', $username)->firstOrFail();
        $statusResponse = StatusBackend::getActiveStatuses($user->id, true);
        return $this->sendResponse($statusResponse);
    }

    public function avatar(string $username): JsonResponse {
        $user = User::where('username', $username)->first();
        if ($user == null) {
            return $this->sendResponse(null);
        }
        $profilePictureResponse = UserBackend::getProfilePicture($user);
        return $this->sendResponse($profilePictureResponse);
    }

    public function PutProfilepicture(Request $request) {
        $avatar                 = $request->getContent();
        $profilePictureResponse = UserBackend::updateProfilePicture($avatar);
        return $this->sendResponse($profilePictureResponse);
    }

    public function PutDisplayname(Request $request) {
        $displayname         = $request->getContent();
        $displaynameResponse = UserBackend::updateDisplayName($displayname);
        return $this->sendResponse(['success' => $displaynameResponse]);
    }

    public function getLeaderboard(): JsonResponse {
        $mapping = function($row) {
            return [
                'username'       => $row->user->username,
                'train_duration' => $row->duration,
                'train_distance' => $row->distance,
                'points'         => $row->points
            ];
        };

        $users    = LeaderboardBackend::getLeaderboard()->map($mapping);
        $friends  = auth()->check() ? LeaderboardBackend::getLeaderboard(onlyFollowings: true)->map($mapping) : null;
        $distance = LeaderboardBackend::getLeaderboard(orderBy: 'distance')->map($mapping);

        return $this->sendResponse([
                                       'users'      => $users,
                                       'friends'    => $friends,
                                       'kilometers' => $distance
                                   ]);
    }

    public function searchUser($searchQuery) {
        return UserBackend::searchUser($searchQuery);
    }
}
