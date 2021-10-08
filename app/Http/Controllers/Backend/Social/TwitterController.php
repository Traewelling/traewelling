<?php

namespace App\Http\Controllers\Backend\Social;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Controller;
use App\Models\SocialLoginProfile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

abstract class TwitterController extends Controller
{
    /**
     * @param User $user
     *
     * @return TwitterOAuth
     * @throws InvalidArgumentException
     * @throws NotConnectedException
     */
    public static function getApi(User $user): TwitterOAuth {
        $sPro = $user?->socialProfile;
        if ($sPro?->twitter_id == null || $sPro?->twitter_token == null || $sPro?->twitter_tokenSecret == null) {
            throw new NotConnectedException();
        }
        return new TwitterOAuth(
            config('trwl.twitter_id'),
            config('trwl.twitter_secret'),
            $user->socialProfile->twitter_token,
            $user->socialProfile->twitter_tokenSecret
        );
    }

    /**
     * Function to create a user with a login-provider.
     * If logged in, the user will have the login-provider added.
     * If a user with corresponding login-provider already exists, it will be returned.
     *
     * @param $getInfo (response of Socialite->user())
     *
     * @return User|RedirectResponse|null model
     */
    public static function createUser($getInfo): User|RedirectResponse|null {
        $identifier = SocialLoginProfile::where('twitter_id', $getInfo->id)->first();

        if (Auth::check()) {
            $user = Auth::user();
            if ($identifier != null) {
                return redirect()->to('/dashboard')->withErrors([__('controller.social.already-connected-error')]);
            }
        } elseif ($identifier === null) {
            $existingUser = User::where('username', $getInfo->nickname)->first();
            $errorCount   = 0;
            while ($errorCount < 10 && $existingUser !== null) {
                $getInfo->nickname = $getInfo->nickname . rand(1, 10);
                $existingUser      = User::where('username', $getInfo->nickname)->first();
                $errorCount++;
            }
            try {
                $user = User::create([
                                         'name'     => $getInfo->name,
                                         'username' => $getInfo->nickname,
                                         'email'    => $getInfo->email,
                                     ]);
            } catch (QueryException) {
                return null;
            }
        } else {
            $user = User::where('id', $identifier->user_id)->first();
        }

        $socialProfile                      = $user->socialProfile ?: new SocialLoginProfile;
        $socialProfile->twitter_id          = $getInfo->id;
        $socialProfile->twitter_token       = $getInfo->token;
        $socialProfile->twitter_tokenSecret = $getInfo->tokenSecret;

        $user->socialProfile()->save($socialProfile);

        return $user;
    }
}
