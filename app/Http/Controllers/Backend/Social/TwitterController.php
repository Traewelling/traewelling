<?php

namespace App\Http\Controllers\Backend;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\Controller;
use App\Models\User;
use InvalidArgumentException;

abstract class TwitterController extends Controller
{
    /**
     * @param User $user
     * @return TwitterOAuth
     * @throws InvalidArgumentException
     */
    public static function getApi(User $user): TwitterOAuth {
        if ($user->socialProfile->twitter_token == null || $user->socialProfile->twitter_tokenSecret == null) {
            throw new InvalidArgumentException('User is not connected to twitter');
        }
        return new TwitterOAuth(
            config('trwl.twitter_id'),
            config('trwl.twitter_secret'),
            $user->socialProfile->twitter_token,
            $user->socialProfile->twitter_tokenSecret
        );
    }
}
