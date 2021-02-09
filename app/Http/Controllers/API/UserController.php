<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group User
 * Stuff for the user I guess
 *
 * @package App\Http\Controllers\API */
class UserController extends ResponseController
{
    /**
     * Get User
     *
     * @urlParam username string required The username of the requested user. Example:gertrud123
     *
     * @responseFile status=200 storage/responses/v0/user.get.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $username
     * @return JsonResponse
     */
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
        return $this->sendResponse($displaynameResponse);
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

        $users   = $leaderboardResponse['users']->take(15)->map($mapping);
        $friends = $leaderboardResponse['friends'] ?->take(15)->map($mapping);
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
