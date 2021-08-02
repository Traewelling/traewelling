<?php

namespace App\Http\Controllers\Backend\Social;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Controller;
use App\Models\User;
use InvalidArgumentException;

abstract class TwitterController extends Controller
{
    /**
     * @param User $user
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
}
