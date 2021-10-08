<?php

namespace App\Http\Controllers\Frontend\Social;

use App\Exceptions\SocialAuth\InvalidMastodonException;
use App\Http\Controllers\Backend\Social\MastodonController as MastodonBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
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
            return Socialite::driver('mastodon')->redirect();
        } catch (Exception $exception) {
            report($exception);
            return back()->with('error', __('messages.exception.general'));
        }
    }

    /**
     * handles callback of login-provider with socialite.
     * Calls createUser
     *
     * @return RedirectResponse
     */
    public function callback(): RedirectResponse {
        $domain = session('mastodon_domain');
        $server = session('mastodon_server');

        config(['services.mastodon.domain' => $domain]);
        config(['services.mastodon.client_id' => $server->client_id]);
        config(['services.mastodon.client_secret' => $server->client_secret]);

        $socialiteUser = Socialite::driver(driver: 'mastodon')->user();
        $user          = MastodonBackend::getUserFromSocialite($socialiteUser, $server);
        if ($user === null) {
            return redirect()->to('/login')->withErrors([__('controller.social.create-error')]);
        }
        if (!auth()->check()) {
            auth()->login(user: $user, remember: true);
            $user->update(['last_login' => Carbon::now()->toIso8601String()]);
        }

        return redirect()->route('dashboard');
    }
}
