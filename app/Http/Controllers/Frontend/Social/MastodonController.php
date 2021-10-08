<?php

namespace App\Http\Controllers\Frontend\Social;

use App\Exceptions\SocialAuth\InvalidMastodonException;
use App\Http\Controllers\Backend\Social\MastodonController as MastodonBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class MastodonController extends Controller
{
    /**
     * Redirects to login-provider authentication
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
     */
    public function redirect(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse {
        $request->request->set('domain', MastodonBackend::formatDomain($request->input('domain')));
        $validated = $request->validate(['domain' => ['required', 'active_url']]);
        $domain    = $validated['domain'];

        try {
            $server = MastodonBackend::getMastodonServer($domain);
        } catch (InvalidMastodonException $exception) {
            report($exception);
            return redirect()->back()->with('error', __('user.invalid-mastodon', ['domain' => $domain]));
        }

        //change config
        config(['services.mastodon.domain' => $domain]);
        config(['services.mastodon.client_id' => $server->client_id]);
        config(['services.mastodon.client_secret' => $server->client_secret]);

        session(['mastodon_domain' => $domain]);
        session(['mastodon_server' => $server]);


        try {
            //TODO: Check and implement
            if ($request->query->get('return', 'none') == 'token') {
                config(['services.mastodon.redirect' => config('services.mastodon.redirect') . '?return=token']);
            }
            return Socialite::driver('mastodon')->redirect();
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
        $domain = session('mastodon_domain');
        $server = session('mastodon_server');

        config(['services.mastodon.domain' => $domain]);
        config(['services.mastodon.client_id' => $server->client_id]);
        config(['services.mastodon.client_secret' => $server->client_secret]);

        $socialiteUser = Socialite::driver('mastodon')->user();
        $user          = MastodonBackend::getUserFromSocialite($socialiteUser, $server);
        if ($user === null) {
            return redirect()->to('/login')->withErrors([__('controller.social.create-error')]);
        }
        if (!auth()->check()) {
            auth()->login($user, true);
            $user->update(['last_login' => Carbon::now()->toIso8601String()]);
        }

        //TODO: Check and implement
        if ($request->query->get('return', 'none') == 'token') {
            $token = $request->user()->createToken('token');
            return response()->json([
                                        'token'      => $token->accessToken,
                                        'expires_at' => $token->token->expires_at->toIso8601String()
                                    ])
                             ->header('Authorization', $token->accessToken);
        }

        return redirect()->route('dashboard');
    }
}
