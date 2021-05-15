<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\Backend\UserController as UserBackend;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function muteUser(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'user_id' => [
                                                'required',
                                                'exists:users,id',
                                                Rule::notIn(auth()->user()->mutedUsers->pluck('id'))
                                            ]
                                        ]);

        $userToBeMuted = User::find($validated['user_id']);

        try {
            $result = UserBackend::muteUser(auth()->user(), $userToBeMuted);
            if ($result) {
                return back()->with('success', __('user.muted', ['username' => $userToBeMuted->username]));
            }
            return back()->with('error', __('messages.exception.general'));
        } catch (UserAlreadyMutedException) {
            return back()->with('error', __('user.already-muted', ['username' => $userToBeMuted->username]));
        }
    }

    public function unmuteUser(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'user_id' => [
                                                'required',
                                                'exists:users,id',
                                                Rule::in(auth()->user()->mutedUsers->pluck('id'))
                                            ]
                                        ]);

        $userToBeUnmuted = User::find($validated['user_id']);

        try {
            $result = UserBackend::unmuteUser(auth()->user(), $userToBeUnmuted);
            if ($result) {
                return back()->with('success', __('user.unmuted', ['username' => $userToBeUnmuted->username]));
            }
            return back()->with('error', __('messages.exception.general'));
        } catch (UserNotMutedException) {
            return back()->with('error', __('user.already-unmuted', ['username' => $userToBeUnmuted->username]));
        }
    }
}
