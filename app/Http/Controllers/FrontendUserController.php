<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController as UserBackend;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Gd\Commands\BackupCommand;

class FrontendUserController extends Controller
{
    public function getProfilePage($username) {
        $profilePage = UserBackend::getProfilePage($username);

        if ($profilePage === null) {
            abort(404);
        }

        return view('profile', [
            'username' => $profilePage['username'],
            'statuses' => $profilePage['statuses'],
            'user' => $profilePage['user']]);
    }

    public function getProfilePicture($username) {
        $profilePicture = UserBackend::getProfilePicture($username);

        if($profilePicture === null) {
            abort(404);
        }

        return response($profilePicture['picture'])
            ->header('Content-Type', 'image/'. $profilePicture['extension'])
            ->header('Cache-Control', 'public, no-transform, max-age:900');
    }

    public function getLeaderboard() {
        $leaderboardResponse = UserBackend::getLeaderboard();

        return view('leaderboard', [
            'users' => $leaderboardResponse['users'],
            'friends' => $leaderboardResponse['friends'],
            'kilometers' => $leaderboardResponse['kilometers']
        ]);
    }

    public function CreateFollow(Request $request) {
        $CreateFollowResponse = UserBackend::CreateFollow(Auth::user(), $request['follow_id']);
        if($CreateFollowResponse === false) {
            return response()->json(['message' => __('controller.user.follow-already-exists')], 409);
        }
        return response()->json(['message' => __('controller.user.follow-ok')], 201);
    }

    public function DestroyFollow(Request $request) {
        $DestroyFollowResponse = UserBackend::DestroyFollow(Auth::user(), $request['follow_id']);
        if($DestroyFollowResponse === false) {
            return response()->json(['message' => __('controller.user.follow-404')], 409);
        }
        return response()->json(['message' => __('controller.user.follow-destroyed')], 200);
    }

    public function updateProfilePicture(Request $request)
    {
        $avatar                 = $request->input('image');
        $profilePictureResponse = UserBackend::updateProfilePicture($avatar);
        return response()->json($profilePictureResponse);
    }

}
