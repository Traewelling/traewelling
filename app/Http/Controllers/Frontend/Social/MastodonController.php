<?php

namespace App\Http\Controllers\Frontend\Social;

use App\Exceptions\SocialAuth\InvalidMastodonException;
use App\Http\Controllers\Backend\Social\MastodonController as MastodonBackend;
use App\Http\Controllers\Controller;
use App\Services\MastodonDomainExtractionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SympfonyRedirectResponse;

class MastodonController extends Controller
{
    /**
     * Redirects to login-provider authentication
     *
     * @param Request $request
     *
     * @return SympfonyRedirectResponse|RedirectResponse
     * @throws ValidationException
     */
    public function redirect(Request $request): SympfonyRedirectResponse|RedirectResponse {
        $domain    = (new MastodonDomainExtractionService)->formatDomain($request->input('domain') ?? '');
        $validator = Validator::make(['domain' => $domain], ['domain' => ['required', 'active_url']]);
        $validated = $validator->validate();

        try {
            $server = MastodonBackend::getMastodonServer($validated['domain']);
        } catch (InvalidMastodonException $exception) {
            report($exception);
            return redirect()->back()->with('error', __('user.invalid-mastodon', [
                'domain' => $validated['domain']
            ]));
        }

        //change config
        config(['services.mastodon.domain' => $server->domain]);
        config(['services.mastodon.client_id' => $server->client_id]);
        config(['services.mastodon.client_secret' => $server->client_secret]);

        session(['mastodon_domain' => $server->domain]);
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

        if (request()->has('error')) {
            return redirect()->route('login')->with('error', \request('error_description'));
        }

        $socialiteUser = Socialite::driver(driver: 'mastodon')->user();
        $user          = MastodonBackend::getUserFromSocialite($socialiteUser, $server);
        if (!auth()->check()) {
            auth()->login(user: $user, remember: true);
            $user->update(['last_login' => Carbon::now()->toIso8601String()]);
        }

        return redirect()->intended('/dashboard');
    }
}
