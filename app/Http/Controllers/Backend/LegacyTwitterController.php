<?php

namespace App\Http\Controllers\Backend;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Backend\Social\AbstractTwitterController;
use App\Http\Controllers\Backend\Social\TweetNotSendException;
use App\Models\Status;
use App\Models\User;
use App\Notifications\TwitterNotSent;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class LegacyTwitterController extends AbstractTwitterController
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
        if ($sPro?->twitter_id === null || $sPro?->twitter_token === null || $sPro->twitter_tokenSecret) {
            throw new NotConnectedException();
        }
        return new TwitterOAuth(
            consumerKey:      config('trwl.twitter_oauth1_id'),
            consumerSecret:   config('trwl.twitter_oauth1_secret'),
            oauthToken:       $user->socialProfile->twitter_token,
            oauthTokenSecret: $user->socialProfile->twitter_tokenSecret
        );
    }

    public function postTweet(Status $status, string $socialText): int {
        if ($status?->user?->socialProfile?->twitter_id === null || config('trwl.post_social') !== true) {
            throw new NotConnectedException();
        }

        $connection = self::getApi($status->user);
        $response   = $connection->post('statuses/update',
                                        [
                                            'status' => $socialText,
                                            'lat'    => $status->trainCheckin->Origin->latitude,
                                            'lon'    => $status->trainCheckin->Origin->longitude
                                        ]
        );

        if ($connection->getLastHttpCode() !== 200) {
            throw new TweetNotSendException($status, $connection->getLastHttpCode());
        }

        return $response->id;
    }
}
