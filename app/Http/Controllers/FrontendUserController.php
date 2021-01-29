<?php

namespace App\Http\Controllers;

use App\Http\Controllers\UserController as UserBackend;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $profilePicture = UserBackend::getProfilePicture($username);

        if ($profilePicture === null) {
            abort(404);
        }

        return response($profilePicture['picture'])
            ->header('Content-Type', 'image/' . $profilePicture['extension'])
            ->header('Cache-Control', 'public, no-transform, max-age:900');
    }

    public function getLeaderboard(): Renderable {
        $leaderboardResponse = UserBackend::getLeaderboard();

        return view('leaderboard', [
            'usersCount' => count($leaderboardResponse['users']),
            'users'      => $leaderboardResponse['users'],
            'friends'    => $leaderboardResponse['friends'],
            'kilometers' => $leaderboardResponse['kilometers']
        ]);
    }

    public function renderMonthlyLeaderboard(string $date): Renderable {
        $date = Carbon::parse($date);

        $leaderboard = Status::join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
                             ->where(
                                 'train_checkins.departure',
                                 '>=',
                                 $date->clone()->firstOfMonth()->toDateString()
                             )
                             ->where(
                                 'train_checkins.departure',
                                 '<=',
                                 $date->clone()->lastOfMonth()->toDateString() . ' 23:59:59'
                             )
                             ->get()
                             ->groupBy('user_id')
                             ->map(function($statuses) {
                                 return [
                                     'user'        => $statuses->first()->user,
                                     'points'      => $statuses->sum('trainCheckin.points'),
                                     'distance'    => $statuses->sum('distance'),
                                     'duration'    => $statuses->sum('trainCheckin.duration'),
                                     'statusCount' => $statuses->count()
                                 ];
                             })
                             ->sortByDesc('points');

        return view('leaderboard.month', [
            'leaderboard' => $leaderboard,
            'date'        => $date
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function CreateFollow(Request $request): JsonResponse {
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
    public function DestroyFollow(Request $request): JsonResponse {
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
        } catch (HttpException $exception) {
            return redirect()->back();
        }

        return view("search", [
            'userSearchResponse' => $userSearchResponse
        ]);
    }
}
