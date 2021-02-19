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
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<binary>> empty response
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<binary>> empty response
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<binary>> empty response
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $username
     * @return JsonResponse
     */
    public function show($username) {
        $userResponse = UserBackend::getProfilePage($username);
        return $this->sendResponse($userResponse);
    }

    /**
     * Get active status
     * Gets the currently active status for a given user
     *
     * @urlParam username string required The username of the requested user. Example:gertrud123
     *
     * @responseFile status=200 storage/responses/v0/user.active.get.json
     * @response 204 scenario="No active status" <<binary>> empty response
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<binary>> empty response
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<binary>> empty response
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<binary>> empty response
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $username
     * @return JsonResponse
     */
    public function active($username) {
        $user           = User::where('username', $username)->firstOrFail();
        $statusResponse = StatusBackend::getActiveStatuses($user->id, true);
        return $this->sendResponse($statusResponse);
    }

    /**
     * @deprecated
     *
     * @param $username
     * @return JsonResponse
     */
    public function avatar($username) {
        $profilePictureResponse = UserBackend::getProfilePicture($username);
        return $this->sendResponse($profilePictureResponse);
    }


    /**
     * Update avatar
     * Gets the avatar of a given user
     *
     * @bodyParam image file required This is actually the body of the request. Scribe won't let me document it like that.
     *
     * @response 200 scenario="OK. The avatar was successfully uploaded." <<binary>> empty response
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<binary>> empty response
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<binary>> empty response
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $username
     * @return JsonResponse
     */
    public function PutProfilepicture(Request $request) {
        $avatar                 = $request->getContent();
        $profilePictureResponse = UserBackend::updateProfilePicture($avatar);
        return $this->sendResponse($profilePictureResponse);
    }

    /**
     * Update DisplayName
     * Updates the display name of the current user
     *
     * @bodyParam username string required This is actually a string in the body, not a json-request.
     *
     * @response 200 scenario="OK. The displayName of the current user was changed." <<binary>> empty response
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<binary>> empty response
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<binary>> empty response
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function PutDisplayname(Request $request) {
        $displayname         = $request->getContent();
        $displaynameResponse = UserBackend::updateDisplayName($displayname);
        return $this->sendResponse(['success' => $displaynameResponse]);
    }

    /**
     * Leaderboard
     * Gets the leaderboard for friends, kilometers and users.
     *
     * @responseFile status=200 storage/responses/v0/leaderboard.get.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<binary>> empty response
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<binary>> empty response
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @return JsonResponse
     */
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
        $friends = $leaderboardResponse['friends'] ?->take(15)->map($mapping);
        $kilometers = $leaderboardResponse['kilometers']->take(15)->map($mapping);

        return $this->sendResponse([
                                       'users'      => $users,
                                       'friends'    => $friends,
                                       'kilometers' => $kilometers
                                   ]);
    }

    /**
     * Search
     * Searches for users with a query
     *
     * @urlParam searchQuery string required The string to be searched for in all registered users
     *
     * @responseFile status=200 storage/responses/v0/search.get.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<binary>> empty response
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<binary>> empty response
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $searchQuery
     * @return mixed
     */
    public function searchUser($searchQuery) {
        return UserBackend::searchUser($searchQuery);
    }
}
