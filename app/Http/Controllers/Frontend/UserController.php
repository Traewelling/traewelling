<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\UserAlreadyBlockedException;
use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotBlockedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\Backend\UserController as UserBackend;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function blockUser(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'user_id' => [
                                                'required',
                                                'exists:users,id',
                                                Rule::notIn(auth()->user()->blockedUsers->pluck('id')),
                                                Rule::notIn([auth()->user()->id]),
                                            ]
                                        ]);

        $userToBeBlocked = User::find($validated['user_id']);

        try {
            $result = UserBackend::blockUser(auth()->user(), $userToBeBlocked);
            if ($result) {
                return back()->with('success', __('user.blocked', ['username' => $userToBeBlocked->username]));
            }
            return back()->with('error', __('messages.exception.general'));
        } catch (UserAlreadyBlockedException) {
            return back()->with('error', __('user.already-blocked', ['username' => $userToBeBlocked->username]));
        }
    }

    public function unblockUser(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'user_id' => [
                                                'required',
                                                'exists:users,id',
                                                Rule::in(auth()->user()->blockedUsers->pluck('id'))
                                            ]
                                        ]);

        $userToBeUnblocked = User::find($validated['user_id']);

        try {
            $result = UserBackend::unblockUser(auth()->user(), $userToBeUnblocked);
            if ($result) {
                return back()->with('success', __('user.unblocked', ['username' => $userToBeUnblocked->username]));
            }
            return back()->with('error', __('messages.exception.general'));
        } catch (UserNotBlockedException) {
            return back()->with('error', __('user.already-unblocked', ['username' => $userToBeUnblocked->username]));
        }
    }

    public function muteUser(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'user_id' => [
                                                'required',
                                                'exists:users,id',
                                                Rule::notIn(auth()->user()->mutedUsers->pluck('id')),
                                                Rule::notIn([auth()->user()->id]),
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
