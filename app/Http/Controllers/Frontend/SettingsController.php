<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SettingsController as SettingsBackend;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;

class SettingsController extends Controller
{

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
                                   'email'      => $validated['email'],
                                   'username'   => $validated['username'],
                                   'name'       => $validated['name'],
                                   'always_dbl' => $request->always_dbl == "on",
                               ]);

        if (!auth()->user()->hasVerifiedEmail()) {
            auth()->user()->sendEmailVerificationNotification();
        }

        return back();
    }

    public function updatePrivacySettings(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'private_profile' => ['nullable'],
                                            'prevent_index'   => ['required', 'gte:0', 'lte:1'],
                                        ]);

        auth()->user()->update([
                                   'prevent_index'   => $validated['prevent_index'],
                                   'private_profile' => isset($validated['private_profile'])
                                       && $validated['private_profile'] == 'on',
                               ]);

        return back()->with('success', __('settings.privacy.update.success'));
    }

    public function updatePassword(Request $request): RedirectResponse {
        $userHasPassword = auth()->user()->password != null;

        $validated = $request->validate([
                                            'currentPassword' => [Rule::requiredIf($userHasPassword)],
                                            'password'        => ['required', 'string', 'min:8', 'confirmed']
                                        ]);

        if ($userHasPassword && !Hash::check($validated['currentPassword'], auth()->user()->password)) {
            return back()->withErrors(__('controller.user.password-wrong'));
        }

        auth()->user()->update([
                                   'password' => Hash::make($validated['password'])
                               ]);

        return back()->with('info', __('controller.user.password-changed-ok'));
    }

    public function renderSettings(): Renderable {
        $sessions = auth()->user()->sessions->map(function($session) {
            $result = new Agent();
            $result->setUserAgent($session->user_agent);
            $session->platform = $result->platform();

            if ($result->isphone()) {
                $session->device_icon = 'mobile-alt';
            } elseif ($result->isTablet()) {
                $session->device_icon = 'tablet';
            } else {
                $session->device_icon = 'desktop';
            }

            return $session;
        });

        return view('settings.settings', [
            'sessions' => $sessions,
            'tokens'   => auth()->user()->tokens->where('revoked', '0')
        ]);
    }

    /**
     * Approve a follow request
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws \App\Exceptions\AlreadyFollowingException
     */
    public function approveFollower(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'user_id' => [
                                                'required',
                                                Rule::in(auth()->user()->followRequests->pluck('user_id'))
                                            ]
                                        ]);

        try {
            $approval = SettingsBackend::approveFollower(auth()->user()->id, $validated['user_id']);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        if ($approval) {
            return back()->with('success', __('settings.request.accept-success'));
        }
        return back()->with('danger', __('messages.exception.general'));
    }

    /**
     * Reject a follow request
     * @param Request $request
     * @return RedirectResponse
     */
    public function rejectFollower(Request $request) {
        $validated = $request->validate([
                                            'user_id' => [
                                                'required',
                                                Rule::in(auth()->user()->followRequests->pluck('user_id'))
                                            ]
                                        ]);
        try {
            $approval = SettingsBackend::rejectFollower(auth()->user()->id, $validated['user_id']);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        if ($approval) {
            return back()->with('success', __('settings.request.reject-success'));
        }
        return back()->with('danger', __('messages.exception.general'));
    }
}
