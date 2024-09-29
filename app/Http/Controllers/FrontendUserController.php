<?php

namespace App\Http\Controllers;

use App\Exceptions\AlreadyFollowingException;
use App\Http\Controllers\Backend\UserController as UserControllerAlias;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendUserController extends Controller
{
    public function getProfilePage(string $username): View {
        $user = User::where('username', $username)->firstOrFail();
        try {
            $statuses = UserController::statusesForUser($user);
        } catch (AuthorizationException) {
            $statuses = null;
        }

        return view('profile', [
            'statuses' => $statuses,
            'user'     => $user,
        ]);
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
            $this->authorize('create', Follow::class);
            $createFollowResponse = UserBackend::createFollow(Auth::user(), $userToFollow);
        } catch (AlreadyFollowingException) {
            return response()->json(['message' => __('controller.user.follow-error')], 409);
        } catch (AuthorizationException) {
            return response()->json(['message' => __('profile.youre-blocked-text')], 403);
        }
        if (!$createFollowResponse) {
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

    public function searchUser(Request $request): Renderable|RedirectResponse {
        try {
            $users = UserControllerAlias::searchUser($request['searchQuery']);
            if ($users->count() === 1) {
                return redirect()->route('profile', ['username' => $users->first()->username]);
            }
            return view('search', [
                'users' => $users,
            ]);
        } catch (HttpException|InvalidArgumentException) {
            //abort(400) is triggered.
            return redirect()->back()->with('error', __('error.bad-request'));
        }
    }
}
