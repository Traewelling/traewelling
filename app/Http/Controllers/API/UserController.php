<?php

namespace App\Http\Controllers\API;

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
        $user           = User::where('username', $username)->firstOrFail();
        $statusResponse = StatusBackend::getActiveStatuses($user->id, true);
        return $this->sendResponse($statusResponse);
    }

    public function avatar($username) {
        $profilePictureResponse = UserBackend::getProfilePicture($username);
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
        $leaderboardResponse = UserBackend::getLeaderboard();
        $mapping             = function($user) {
            return [
                'username'       => $user['user']->username,
                'train_duration' => $user['duration'],
                'train_distance' => $user['distance'],
                'points'         => $user['points']
            ];
        };

        $users      = $leaderboardResponse['users']->take(15)->map($mapping);
        $friends    = $leaderboardResponse['friends']?->take(15)->map($mapping);
        $kilometers = $leaderboardResponse['kilometers']->take(15)->map($mapping);

        return $this->sendResponse([
                                       'users'      => $users,
                                       'friends'    => $friends,
                                       'kilometers' => $kilometers
                                   ]);
    }

    public function searchUser($searchQuery) {
        return UserBackend::searchUser($searchQuery);
    }
}
