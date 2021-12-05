<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * @todo Revert this PR after 2022-01-06
 */
class ChristmasController extends Controller
{

    public static function toggleChristmasMode(Request $request): RedirectResponse {
        $validated = $request->validate(['christmas-mode' => ['required', 'boolean']]);
        session()->put('christmas-mode', $validated['christmas-mode'] === "1");
        return back()->with('alert-info', __('merry-christmas'));
    }
}
