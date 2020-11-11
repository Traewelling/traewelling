<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController as UserBackend;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FrontendUserController extends Controller
{
    public function getProfilePage($username)
    {
        $profilePage = UserBackend::getProfilePage($username);
        if ($profilePage === null) {
            abort(404);
        }

        return view('profile', [
            'username' => $profilePage['username'],
            'statuses' => $profilePage['statuses'],
            'user' => $profilePage['user'],
            'currentUser' => Auth::user(),
            'twitterUrl' => $profilePage['twitterUrl'],
            'mastodonUrl' => $profilePage['mastodonUrl']
        ]);
    }

    public function getProfilePicture($username)
    {
        $profilePicture = UserBackend::getProfilePicture($username);

        if($profilePicture === null) {
            abort(404);
        }

        return response($profilePicture['picture'])
            ->header('Content-Type', 'image/'. $profilePicture['extension'])
            ->header('Cache-Control', 'public, no-transform, max-age:900');
    }

    public function getLeaderboard()
    {
        $leaderboardResponse = UserBackend::getLeaderboard();

        return view('leaderboard', [
            'usersCount' => count($leaderboardResponse['users']),
            'users' => $leaderboardResponse['users'],
            'friends' => $leaderboardResponse['friends'],
            'kilometers' => $leaderboardResponse['kilometers']
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function CreateFollow(Request $request) {
        $validated = $request->validate([
                                            'follow_id' => ['required', 'exists:users,id']
                                        ]);

        $userToFollow = User::find($validated['follow_id']);

        $createFollowResponse = UserBackend::createFollow(Auth::user(), $userToFollow);
        if ($createFollowResponse === false) {
            return response()->json(['message' => __('controller.user.follow-already-exists')], 409);
        }
        return response()->json(['message' => __('controller.user.follow-ok')], 201);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function DestroyFollow(Request $request) {
        $validated      = $request->validate([
                                                 'follow_id' => ['required', 'exists:users,id']
                                             ]);
        $userToUnfollow = User::find($validated['follow_id']);

        $destroyFollowResponse = UserBackend::destroyFollow(Auth::user(), $userToUnfollow);
        if ($destroyFollowResponse === false) {
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

    public function searchUser(Request $request) {
        try {
            $userSearchResponse = UserBackend::searchUser($request['searchQuery']);
        } catch (HttpException $exception) {
            return redirect()->back();
        }

        return view("search", [
            'userSearchResponse' => $userSearchResponse
        ]);
    }
}
