<?php

namespace App\Http\Controllers;

use App\SocialLoginProfile;
use Illuminate\Http\Request;
use Validator,Redirect,Response,File;
use Socialite;
use App\User;
use App\MastodonServer;
use Auth;
use Mastodon;

class SocialController extends Controller
{
    /**
     * Redirects to login-provider authentication
     *
     * @param $provider
     *
     * @return redirect
     */
    public function redirect($provider, Request $request) {

        $this->validate($request, [
            'domain' => 'url'
        ]);

        if ($provider === 'mastodon') {
            //input domain by user
            $domain = $request->input('domain');

            //get app info. domain, client_id, client_secret ...
            //Server is Eloquent Model
            $server = MastodonServer::where('domain', $domain)->first();

            if (empty($server)) {
                //create new app
                $info = Mastodon::domain($domain)->createApp(env('MASTODON_APPNAME'), env('MASTODON_REDIRECT'), 'write read');

                //save app info
                $server = MastodonServer::create([
                                             'domain'        => $domain,
                                             'client_id'     => $info['client_id'],
                                             'client_secret' => $info['client_secret'],
                                         ]);
            }

            //change config
            config(['services.mastodon.domain' => $domain]);
            config(['services.mastodon.client_id' => $server->client_id]);
            config(['services.mastodon.client_secret' => $server->client_secret]);

            session(['mastodon_domain' => $domain]);
            session(['mastodon_server' => $server]);

        }
        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * handles callback of login-provider with socialite.
     * Calls createUser
     *
     * @param $provider
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback($provider)
    {
        $domain = '';
        if ($provider === 'mastodon') {
            $domain = session('mastodon_domain');
            $server = session('mastodon_server');

            config(['services.mastodon.domain' => $domain]);
            config(['services.mastodon.client_id' => $server->client_id]);
            config(['services.mastodon.client_secret' => $server->client_secret]);

        }


        $getInfo = Socialite::driver($provider)->user();
        $user = $this->createUser($getInfo, $provider, $domain);
        if ($user === null) {
            return redirect()->to('/login')->withErrors([ __('controller.social.create-error')]);
        }
        if(!Auth::check()) {
            auth()->login($user, true);
        }

        return redirect()->to('/dashboard');

    }

    /**
     * Function to create a user with a login-provider.
     * If logged in, the user will have the login-provider added.
     * If a user with corresponding login-provider already exists, it will be returned.
     *
     * @param $getInfo (response of Socialite->user())
     * @param $provider (String of login-provider)
     *
     * @return user model
     */
    function createUser($getInfo, $provider, $domain){
        if ($provider === 'mastodon') {
            $identifier = SocialLoginProfile::where($provider.'_id', $getInfo->id)->where('mastodon_server', MastodonServer::where('domain', $domain)->first()->id)->first();
        } else {
            $identifier = SocialLoginProfile::where($provider.'_id', $getInfo->id)->first();
        }

        if (Auth::check()) {
            $user = Auth::user();
            if ($identifier != null) {
                return redirect()->to('/dashboard')->withErrors([__('controller.social.already-connected-error')]);
            }
        } elseif ($identifier === null) {
            try{
                $user = User::create([
                                         'name' => $getInfo->name,
                                         'username' => $getInfo->nickname,
                                         'email' => $getInfo->email,
                                     ]);
            }
            catch (\Illuminate\Database\QueryException $exception) {
                return null;
            }
        } else {
            $user = User::where('id', $identifier->user_id)->first();
        }

        $socialProfile = $user->socialProfile ?: new SocialLoginProfile;
        $providerField = "{$provider}_id";
        $socialProfile->{$providerField} = $getInfo->id;

        if ($provider === 'twitter') {
            $socialProfile->twitter_token = $getInfo->token;
            $socialProfile->twitter_tokenSecret = $getInfo->tokenSecret;
        }
        if ($provider === 'mastodon') {
            $socialProfile->mastodon_token = $getInfo->token;
            $socialProfile->mastodon_server = MastodonServer::where('domain', $domain)->first()->id;
        }

        $user->socialProfile()->save($socialProfile);


        return $user;
    }

    public function destroyProvider(Request $request) {
        $providerField = "{$request->provider}_id";
        $user = Auth::user();
        if ($user->password === null) {
            return response(__('controller.social.delete-set-password'), 406);
        }

        $SocialLoginProfile = SocialLoginProfile::where('user_id', $user->id)->first();
        if ($SocialLoginProfile === null) {
            return response(__('controller.social.delete-never-connected'), 404);
        }
        $SocialLoginProfile->{$providerField} = '';
        $user->socialProfile()->save($SocialLoginProfile);

        return response(__('controller.social.deleted'), 200);
    }

    public function testMastodon() {
        $user = Auth::user();
        $socialProfile = $user->socialProfile;
        $mastodonDomain = MastodonServer::where('id', $socialProfile->mastodon_server)->first()->domain;

        Mastodon::domain($mastodonDomain)->token($socialProfile->mastodon_token);
        $response = Mastodon::createStatus('test1');
        dd($response);
    }
}
