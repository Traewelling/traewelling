<?php

namespace App\Http\Controllers\Frontend\Social;

use App\Http\Controllers\Backend\Social\AbstractTwitterController as TwitterBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Providers\AuthServiceProvider;

class TwitterController extends Controller
{
    /**
     * Redirects to login-provider authentication
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function redirect(Request $request): RedirectResponse {
        try {
            if ($request->query->get('return', 'none') === 'token') {
                config(['services.twitter.redirect' => config('trwl.twitter_redirect') . '?return=token']);
            }
            return Socialite::driver('twitter-oauth-2')->scopes(['tweet.write', 'users.read', 'offline.access'])->redirect();
        } catch (Exception $exception) {
            report($exception);
            return back()->with('error', __('messages.exception.general'));
        }
    }

    /**
     * Handles callback of login-provider with Socialite.
     *
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse JSON if ?return=token, otherwise Redirect
     */
    public function callback(Request $request): JsonResponse|RedirectResponse {
        $socialiteUser = Socialite::driver(driver: 'twitter-oauth-2')->user();
        $user          = TwitterBackend::getUserFromSocialite($socialiteUser);

        if ($user === null) {
            return redirect()->to('/login')->withErrors([__('controller.social.create-error')]);
        }

        if (!auth()->check()) {
            auth()->login(user: $user, remember: true);
            $user->update(['last_login' => Carbon::now()->toIso8601String()]);
        }

        // ToDo: Remove this if as soon as it's verified that nobody uses it or oAuth is implemented
        if ($request->query->get('return', 'none') === 'token') {
            $token = $request->user()->createToken('token', array_keys(AuthServiceProvider::$scopes));
            return response()->json([
                                        'token'      => $token->accessToken,
                                        'expires_at' => $token->token->expires_at->toIso8601String(),
                                    ])
                             ->header('Authorization', $token->accessToken);
        }

        return redirect()->intended('/dashboard');
    }
}
