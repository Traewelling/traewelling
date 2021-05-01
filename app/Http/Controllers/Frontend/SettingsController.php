<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $validated = $request->validate([
                                            'currentPassword' => ['required'],
                                            'password'        => ['required', 'string', 'min:8', 'confirmed']
                                        ]);

        if (!Hash::check($validated['currentPassword'], auth()->user()->password)) {
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
}
