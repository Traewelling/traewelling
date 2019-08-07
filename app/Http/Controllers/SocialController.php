<?php

namespace App\Http\Controllers;

use App\SocialLoginProfile;
use Illuminate\Http\Request;
use Validator,Redirect,Response,File;
use Socialite;
use App\User;
use Auth;
class SocialController extends Controller
{
    /**
     * Redirects to login-provider authentication
     *
     * @param $provider
     *
     * @return redirect
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
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

        $getInfo = Socialite::driver($provider)->user();
        $user = $this->createUser($getInfo, $provider);
        if(!Auth::check()) {
            auth()->login($user);
        }

        return redirect()->to('/home');

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
    function createUser($getInfo,$provider){

        $identifier = SocialLoginProfile::where($provider.'_id', $getInfo->id)->first();

        if (Auth::check()) {
            $user = Auth::user();
            if ($identifier != null) {
                return redirect()->to('/home')->withErrors(['msg', __('This Account is already connected to another user')]);
            }
        } elseif ($identifier === null) {
            $user = User::create([
                            'name' => $getInfo->name,
                            'username' => $getInfo->nickname,
                            'email' => request($getInfo->email),
                                     ]);
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

        $user->socialProfile()->save($socialProfile);


        return $user;
    }
}
