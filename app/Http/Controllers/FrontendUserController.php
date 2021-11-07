<?php

namespace App\Http\Controllers;

use App\Exceptions\AlreadyFollowingException;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendUserController extends Controller
{
    public function getProfilePage($username): Renderable {
        $profilePage = UserBackend::getProfilePage($username);
        if ($profilePage === null) {
            abort(404);
        }

        return view('profile', [
            'username'    => $profilePage['username'],
            'statuses'    => $profilePage['statuses'],
            'user'        => $profilePage['user'],
            'currentUser' => Auth::user(),
            'twitterUrl'  => $profilePage['twitterUrl'],
            'mastodonUrl' => $profilePage['mastodonUrl']
        ]);
    }

    public function getProfilePicture($username) {
        $user = User::where('username', $username)->firstOrFail();

        $profilePicture = UserBackend::getProfilePicture($user);

        if ($profilePicture === null) {
            abort(404);
        }

        return response($profilePicture['picture'])
            ->header('Content-Type', 'image/' . $profilePicture['extension'])
            ->header('Cache-Control', 'public, no-transform, max-age:900');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @deprecated
     */
    public function CreateFollow(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'follow_id' => ['required', 'exists:users,id']
                                        ]);

        $userToFollow = User::find($validated['follow_id']);

        try {
            $createFollowResponse = UserBackend::createFollow(Auth::user(), $userToFollow);
        } catch (AlreadyFollowingException) {
            return response()->json(['message' => __('controller.user.follow-error')], 409);
        }
        if ($createFollowResponse == false) {
            abort(409);
        }
        return response()->json(['message' => __('controller.user.follow-ok')], 201);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function requestFollow(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'follow_id' => ['required', 'exists:users,id']
                                        ]);

        $userToFollow = User::find($validated['follow_id']);

        try {
            $createFollowResponse = UserBackend::requestFollow(Auth::user(), $userToFollow);
        } catch (AlreadyFollowingException) {
            return response()->json(['message' => __('controller.user.follow-request-already-exists')], 409);
        }
        if ($createFollowResponse === false) {
            abort(409);
        }
        return response()->json(['message' => __('controller.user.follow-request-ok')], 201);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @deprecated
     */
    public function destroyFollow(Request $request): JsonResponse {
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

    public function updateProfilePicture(Request $request): JsonResponse {
        $avatar                 = $request->input('image');
        $profilePictureResponse = UserBackend::updateProfilePicture($avatar);
        return response()->json($profilePictureResponse);
    }

    public function searchUser(Request $request): Renderable|RedirectResponse {
        try {
            $userSearchResponse = UserBackend::searchUser($request['searchQuery']);

            if ($userSearchResponse->count() === 1) {
                return redirect()->route('profile', ['username' => $userSearchResponse->first()->username]);
            }
        } catch (HttpException) {
            return redirect()->back();
        }

        return view('search', [
            'userSearchResponse' => $userSearchResponse
        ]);
    }
}
