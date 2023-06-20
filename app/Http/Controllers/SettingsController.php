<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class SettingsController extends Controller
{
    /**
     * @deprecated
     */
    public function renderFollowerSettings(): Renderable {
        return view('settings.follower', [
            'requests'  => auth()->user()->followRequests()->with('user')->paginate(15),
            'followers' => auth()->user()->followers()->with('user')->paginate(15)
        ]);
    }

    /**
     * @throws AuthorizationException
     * @deprecated
     */
    public function removeFollower(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'user_id' => [
                                                'required',
                                                Rule::in(auth()->user()->followers->pluck('user_id')),
                                            ]
                                        ]);

        $follow = Follow::where('user_id', $validated['user_id'])
                        ->where('follow_id', auth()->user()->id)
                        ->firstOrFail();

        $this->authorize('delete', $follow);
        $follow->delete();

        return back()->with('success', __('settings.follower.delete-success'));
    }
}
