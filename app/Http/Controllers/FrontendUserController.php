<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendUserController extends Controller
{
    public function getProfilePage(string $username): View
    {
        $user = User::where('username', $username)->firstOrFail();

        return view('vue.spa', [
            'title' => $user->name,
            'canonical' => route('profile', ['username' => $user->username]),
            'robots' => $user->prevent_index ? 'noindex' : null,
            'description' => $user->prevent_index ? null : __('description.profile', [
                'username' => $user->name,
                'kmAmount' => number($user->train_distance / 1000, 0),
                'hourAmount' => number($user->train_duration / 60, 0),
            ]),
        ]);
    }
}
