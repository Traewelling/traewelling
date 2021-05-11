<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function renderFollowerSettings(): Renderable {
        return view('settings.follower', [
            'requests'  => auth()->user()->followRequests()->paginate(15),
            'followers' => auth()->user()->followers()->paginate(15)
        ]);
    }

    public function removeFollower(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'user_id' => ['required', Rule::in(auth()->user()->followers->pluck('id'))]
                                        ]);

        $follow = Follow::where('user_id', $validated['user_id'])
                        ->where('follow_id', auth()->user()->id)
                        ->firstOrFail();

        $this->authorize('delete', $follow);
        $follow->delete();

        return back()->with('success', __('settings.follower.delete-success'));
    }

    /**
     *
     * @param int $userId The id of the user who is approving a follower
     * @param int $approverId The id of a to-be-approved follower
     * @throws ModelNotFoundException|\App\Exceptions\AlreadyFollowingException
     */
    public static function approveFollower(int $userId, int $approverId): bool {
        $request = FollowRequest::where('user_id', $approverId)->where('follow_id', $userId)->firstOrFail();

        $follow = UserController::createFollow($request->user, $request->requestedFollow, true);

        if ($follow) {
            $request->delete();
        }
        return $follow;
    }

    /**
     * @param int $userId
     * @param int $followerID
     * @return FollowRequest|null
     */
    public static function rejectFollower(int $userId, int $followerID): ?FollowRequest {
        $request = FollowRequest::where('user_id', $followerID)->where('follow_id', $userId)->firstOrFail();

        $request->delete();
        return $request;

    }
}
