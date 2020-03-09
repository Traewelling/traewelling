<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;

class UserController extends ResponseController
{
    public function show ($username) {
        $UserResponse = UserBackend::getProfilePage($username);
        return $this->sendResponse($UserResponse);
    }

    public function active($username) {
        $user = User::where('username', $username)->firstOrFail();
        $StatusResponse = StatusBackend::getActiveStatuses($user->id);
        return $this->sendResponse($StatusResponse);
    }

    public function avatar($username) {
        $ProfilePictureResponse = UserBackend::getProfilePicture($username);
        return $this->sendResponse($ProfilePictureResponse);
    }

    public function PutProfilepicture(Request $request) {
        $avatar = $request->getContent();
        $ProfilePictureResponse = UserBackend::updateProfilePicture($avatar);
        return $this->sendResponse($ProfilePictureResponse);
    }

    public function PutDisplayname(Request $request) {
        $displayname = $request->getContent();
        $DisplaynameResponse = UserBackend::updateDisplayName($displayname);
        return $this->sendResponse($DisplaynameResponse);
    }

}
