<?php

namespace App\Http\Controllers\Frontend\Social;

use App\Http\Controllers\Controller;
use App\Models\MastodonServer;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Revolution\Mastodon\Facades\Mastodon;

class MastodonController extends Controller
{
    /**
     * Redirects to login-provider authentication
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
     * @throws ValidationException
     */
    public function redirect(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse {

        // If a user tries to login with mastodon and the domain doesn't start with https,
        // then add a 'https://' beforehand.
        if (substr($request->input('domain'), 0, 8) !== "https://") {
            $request->request->set('domain', "https://" . $request->input('domain'));
        }

        $this->validate($request, [
            'domain' => 'url'
        ]);

        //input domain by user
        $domain = $request->input('domain');

        //get app info. domain, client_id, client_secret ...
        //Server is Eloquent Model
        $server = MastodonServer::where('domain', $domain)->first();

        if (empty($server)) {
            try {
                //create new app
                $info = Mastodon::domain($domain)->createApp(config('trwl.mastodon_appname'), config('trwl.mastodon_redirect'), 'write read');

                //save app info
                $server = MastodonServer::create([
                                                     'domain'        => $domain,
                                                     'client_id'     => $info['client_id'],
                                                     'client_secret' => $info['client_secret'],
                                                 ]);
            } catch (ClientException) {
                return redirect()->back()->with('error', __('user.invalid-mastodon', ['domain' => $domain]));
            }
        }
        //If we ever run into a reset of Mastodon AppKeys (#), then this recreates the keys.
        //Keys have to be set to 0 in the database, since the fields are covered by NOT NULL constraint
        if ($server->client_id <= 1 || $server->client_secret <= 1) {
            try {
                //create new app
                $info = Mastodon::domain($domain)->createApp(config('trwl.mastodon_appname'), config('trwl.mastodon_redirect'), 'write read');

                //save app info
                $server->update([
                                    'client_id'     => $info['client_id'],
                                    'client_secret' => $info['client_secret'],
                                ]);
            } catch (ClientException) {
                return redirect()->back()->with('error', __('user.invalid-mastodon', ['domain' => $domain]));
            }

        }

        //change config
        config(['services.mastodon.domain' => $domain]);
        config(['services.mastodon.client_id' => $server->client_id]);
        config(['services.mastodon.client_secret' => $server->client_secret]);

        session(['mastodon_domain' => $domain]);
        session(['mastodon_server' => $server]);


        try {
            return Socialite::driver('mastodon')->redirect();
        } catch (Exception) {
            abort(404);
        }
    }

    /**
     * handles callback of login-provider with socialite.
     * Calls createUser
     *
     * @param $provider
     *
     * @return RedirectResponse
     */
    public function callback($provider): RedirectResponse {
        $domain = session('mastodon_domain');
        $server = session('mastodon_server');

        config(['services.mastodon.domain' => $domain]);
        config(['services.mastodon.client_id' => $server->client_id]);
        config(['services.mastodon.client_secret' => $server->client_secret]);


        $getInfo = Socialite::driver('mastodon')->user();
        $user    = $this->createUser($getInfo, $provider, $domain);
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
