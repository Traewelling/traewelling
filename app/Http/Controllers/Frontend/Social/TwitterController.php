<?php

namespace App\Http\Controllers\Frontend\Social;

use App\Http\Controllers\Backend\Social\TwitterController as TwitterBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function redirect(Request $request): RedirectResponse {
        try {
            //TODO: Check and implement
            if ($request->query->get('return', 'none') == 'token') {
                config(['services.twitter.redirect' => env('TWITTER_REDIRECT') . '?return=token']);
            }
            return Socialite::driver('twitter')->redirect();
        } catch (Exception) {
            abort(404);
        }
    }

    /**
     * handles callback of login-provider with socialite.
     * Calls createUser
     *
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function callback(Request $request): JsonResponse|RedirectResponse {
        $getInfo = Socialite::driver('twitter')->user();
        $user    = TwitterBackend::createUser($getInfo);
        if ($user === null) {
            return redirect()->to('/login')->withErrors([__('controller.social.create-error')]);
        }
        if (!Auth::check()) {
            auth()->login($user, true);
            $user->update(['last_login' => Carbon::now()->toIso8601String()]);
        }

        //TODO: Check and implement
        if ($request->query->get('return', 'none') == 'token') {
            $token = $request->user()->createToken('token');
            return response()->json(                                                                                                                                                                                                                                 [
                                                                                                                                                                                                                                                                      'token'      => $token->accessToken,
                                                                                                                                                                                                                                                                      'expires_at' => $token->token->expires_at->toIso8601String()
                                                                                                                                                                                                                                                                  ], 200)
                             ->header('Authorization', $token->accessToken);
        }

        return redirect()->route('dashboard');
    }
}
