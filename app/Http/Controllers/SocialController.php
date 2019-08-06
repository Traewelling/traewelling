<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator,Redirect,Response,File;
use Socialite;
use App\User;
class SocialController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {

        $getInfo = Socialite::driver($provider)->user();

        $user = User::where('provider_id', $getInfo->id)->first();

        if (!$user) {
            return view('auth/completeRegistration', compact('user', 'getInfo', 'provider'));
        }

        auth()->login($user);

        return redirect()->to('/home');

    }

    function createUser($getInfo,$provider){

        $user = User::where('provider_id', $getInfo->id)->first();

        if (!$user) {

            $user = User::create([
                                     'name'     => $getInfo->name,
                                     'email'    => $getInfo->email,
                                     'provider' => $provider,
                                     'provider_id' => $getInfo->id
                                 ]);
        }
        return $user;
    }

    public function completeRegistration(Request $request) {

        $request->validate([
            'name' => 'required|max:25|alpha_num',
            'username' => 'required|max:25|unique:users',
            'email' => 'email|nullable',
                           ]);

        $user = User::create([
                                 'name'     => $request->name,
                                 'username' => $request->username,
                                 'email'    => request($request->email),
                                 'provider' => $request->provider,
                                 'provider_id' => $request->provider_id
                             ]);

        auth()->login($user);
        return redirect()->to('/home');
    }
}
