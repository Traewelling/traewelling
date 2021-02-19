<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function renderFollowerSettings(): Renderable {
        return view('settings.follower', [
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
}
