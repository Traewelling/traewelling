<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
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
}
