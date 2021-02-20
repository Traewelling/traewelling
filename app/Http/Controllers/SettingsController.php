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

    public function updateMainSettings(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'username' => ['required', 'string', 'max:25', 'regex:/^[a-zA-Z0-9_]*$/'],
                                            'name'     => ['required', 'string', 'max:50'],
                                            'email'    => ['required', 'string', 'email', 'max:255'],
                                            'avatar'   => 'image'
                                        ]);

        if (auth()->user()->username != $request->username) {
            $request->validate(['username' => ['unique:users']]);
        }
        if (auth()->user()->email != $request->email) {
            $request->validate(['email' => ['unique:users']]);
            auth()->user()->update(['email_verified_at' => null]);
        }
        auth()->user()->update([
                                   'email'           => $validated['email'],
                                   'username'        => $validated['username'],
                                   'name'            => $validated['name'],
                                   'always_dbl'      => $request->always_dbl == "on",
                                   'private_profile' => $request->private_profile == "on",
                               ]);

        if (!auth()->user()->hasVerifiedEmail()) {
            auth()->user()->sendEmailVerificationNotification();
        }

        return back();
    }
}
