<?php

namespace App\Http\Controllers\Frontend\Social;

use App\Http\Controllers\Backend\Social\TwitterController as TwitterBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TwitterController extends Controller
{
    /**
     * Redirects to login-provider authentication
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse {
        try {
            return Socialite::driver('twitter')->redirect();
        } catch (Exception) {
            abort(404);
        }
    }

    /**
     * handles callback of login-provider with socialite.
     * Calls createUser
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(): RedirectResponse {
        $getInfo = Socialite::driver('twitter')->user();
        $user    = TwitterBackend::createUser($getInfo);
        if ($user === null) {
            return redirect()->to('/login')->withErrors([__('controller.social.create-error')]);
        }
        if (!Auth::check()) {
            auth()->login($user, true);
            $user->update(['last_login' => Carbon::now()->toIso8601String()]);
        }

        return redirect()->route('dashboard');
    }
}
