<?php

namespace App\Http\Controllers\Frontend;

use App\Enum\MastodonVisibility;
use App\Enum\StatusVisibility;
use App\Exceptions\AlreadyFollowingException;
use App\Http\Controllers\Backend\User\FollowController;
use App\Http\Controllers\Backend\User\FollowController as SettingsBackend;
use App\Http\Controllers\Backend\User\SessionController;
use App\Http\Controllers\Backend\User\TokenController;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class SettingsController extends Controller
{
    /**
     * @deprecated
     */
    public function renderFollowerSettings(): Renderable
    {
        return view('settings.follower', [
            'requests' => auth()->user()->followRequests()->with('user')->paginate(15),
            'followers' => auth()->user()->followers()->with('user')->paginate(15),
        ]);
    }

    public function renderBlockedUsers(): Renderable
    {
        return view('settings.blocks');
    }

    public function renderMutedUsers(): Renderable
    {
        return view('settings.mutes');
    }

    public function updatePrivacySettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'likes_enabled' => ['nullable'],
            'points_enabled' => ['nullable'],
            'private_profile' => ['nullable'],
            'prevent_index' => ['required', 'gte:0', 'lte:1'],
            'privacy_hide_days' => ['nullable', 'gte:1'],
            'default_status_visibility' => [
                'required',
                new Enum(StatusVisibility::class),
            ],
            'mastodon_visibility' => [
                'required',
                new Enum(MastodonVisibility::class),
            ],
        ]);

        $user = auth()->user();
        $user->update([
            'likes_enabled' => isset($validated['likes_enabled'])
                                           && $validated['likes_enabled'] === 'on',
            'points_enabled' => isset($validated['points_enabled'])
                                           && $validated['points_enabled'] === 'on',
            'prevent_index' => $validated['prevent_index'],
            'private_profile' => isset($validated['private_profile'])
                                           && $validated['private_profile'] === 'on',
            'privacy_hide_days' => $validated['privacy_hide_days'] ?? null,
            'default_status_visibility' => $validated['default_status_visibility'],
        ]);

        $user->socialProfile->update(['mastodon_visibility' => $validated['mastodon_visibility']]);

        return back()->with('success', __('settings.privacy.update.success'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $userHasPassword = auth()->user()->password != null;

        $validated = $request->validate([
            'currentPassword' => [Rule::requiredIf($userHasPassword)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($userHasPassword && !Hash::check($validated['currentPassword'], auth()->user()->password)) {
            return back()->withErrors(__('controller.user.password-wrong'));
        }

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('info', __('controller.user.password-changed-ok'));
    }

    public function renderProfile(): Renderable
    {
        return view('settings.profile');
    }

    public function renderPrivacy(): Renderable
    {
        return view('settings.privacy');
    }

    public function renderLoginProviders(): Renderable
    {
        return view('settings.login-providers');
    }

    public function renderSessions(): Renderable
    {
        return view('settings.sessions', [
            'sessions' => SessionController::index(user: auth()->user()),
        ]);
    }

    public function renderIcs(): Renderable
    {
        return view('settings.ics');
    }

    public function renderToken(): Renderable
    {
        return view('settings.api-token', [
            'tokens' => TokenController::index(user: auth()->user()),
        ]);
    }

    /**
     * Approve a follow request
     *
     *
     * @throws AlreadyFollowingException
     */
    public function approveFollower(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::in(auth()->user()->followRequests->pluck('user_id')),
            ],
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
     */
    public function rejectFollower(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::in(auth()->user()->followRequests->pluck('user_id')),
            ],
        ]);
        try {
            $approval = FollowController::rejectFollower(auth()->user()->id, $validated['user_id']);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        if ($approval) {
            return back()->with('success', __('settings.request.reject-success'));
        }

        return back()->with('danger', __('messages.exception.general'));
    }
}
