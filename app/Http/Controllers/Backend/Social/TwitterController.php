<?php

namespace App\Http\Controllers\Backend\Social;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Controller;
use App\Models\SocialLoginProfile;
use App\Models\User;
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
     * @param \Laravel\Socialite\Contracts\User $socialiteUser
     *
     * @return User model
     */
    public static function getUserFromSocialite(\Laravel\Socialite\Contracts\User $socialiteUser): User {
        $socialProfile = SocialLoginProfile::where('twitter_id', $socialiteUser->id)->first();

        if ($socialProfile === null) {
            if (auth()->check()) {
                self::updateToken(auth()->user(), $socialiteUser);
                return $socialProfile->user;
            } else {
                return self::createUser($socialiteUser);
            }
        } else {
            //TODO: User should be logged in as this user
            self::updateToken($socialProfile->user, $socialiteUser);
            return $socialProfile->user;
        }
    }

    private static function createUser(\Laravel\Socialite\Contracts\User $socialiteUser): User {
        //TODO: Fetch profile image (see issue #5)
        $user = User::create([
                                 'name'     => self::getDisplayName($socialiteUser),
                                 'username' => self::getUniqueUsername($socialiteUser->getNickname()),
                             ]);
        self::updateToken($user, $socialiteUser);
        return $user;
    }

    private static function getDisplayName(\Laravel\Socialite\Contracts\User $socialiteUser): string {
        if (trim($socialiteUser->getName()) == '') {
            return $socialiteUser->getNickname();
        }
        return $socialiteUser->getName();
    }

    /**
     * @param string $username
     *
     * @return string
     * @todo Kann ausgelagert werden -> ist was allgemeines (Mastodon?)
     */
    private static function getUniqueUsername(string $username): string {
        $existingUser = User::where('username', $username)->first();
        $errorCount   = 0;
        while ($errorCount < 10 && $existingUser !== null) {
            $username     = $username . rand(1, 10);
            $existingUser = User::where('username', $username)->first();
            $errorCount++;
        }
        return $username;
    }

    private static function updateToken(User $user, \Laravel\Socialite\Contracts\User $socialiteUser) {
        $user->socialProfile->update([
                                         'twitter_id'          => $socialiteUser->id,
                                         'twitter_token'       => $socialiteUser->token,
                                         'twitter_tokenSecret' => $socialiteUser->tokenSecret,
                                     ]);
    }
}
