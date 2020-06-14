<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;

class UserController extends ResponseController
{
    public function show ($username)
    {
        $userResponse = UserBackend::getProfilePage($username);
        return $this->sendResponse($userResponse);
    }

    public function active($username)
    {
        $user           = User::where('username', $username)->firstOrFail();
        $statusResponse = StatusBackend::getActiveStatuses($user->id, true);
        return $this->sendResponse($statusResponse);
    }

    public function avatar($username)
    {
        $profilePictureResponse = UserBackend::getProfilePicture($username);
        return $this->sendResponse($profilePictureResponse);
    }

    public function PutProfilepicture(Request $request)
    {
        $avatar                 = $request->getContent();
        $profilePictureResponse = UserBackend::updateProfilePicture($avatar);
        return $this->sendResponse($profilePictureResponse);
    }

    public function PutDisplayname(Request $request)
    {
        $displayname         = $request->getContent();
        $displaynameResponse = UserBackend::updateDisplayName($displayname);
        return $this->sendResponse($displaynameResponse);
    }

    public function getLeaderboard(Request $request) {
        $leaderboardResponse = UserBackend::getLeaderboard();

        return $this->sendResponse([
            'usersCount' => count($leaderboardResponse['users']),
            'users' => $leaderboardResponse['users'],
            'friends' => $leaderboardResponse['friends'],
            'kilometers' => $leaderboardResponse['kilometers']
        ]);
    }
}
