<?php

namespace App\Http\Controllers\Backend;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Backend\Social\AbstractTwitterController;
use App\Exceptions\SocialAuth\TweetNotSendException;
use App\Models\Status;
use App\Models\User;
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
        $socialProfile = $user?->socialProfile;
        if ($socialProfile?->twitter_id === null
            || $socialProfile?->twitter_token === null
            || $socialProfile->twitter_tokenSecret === null) {
            throw new NotConnectedException();
        }
        return new TwitterOAuth(
            consumerKey:      config('trwl.twitter_oauth1_id'),
            consumerSecret:   config('trwl.twitter_oauth1_secret'),
            oauthToken:       $user->socialProfile->twitter_token,
            oauthTokenSecret: $user->socialProfile->twitter_tokenSecret
        );
    }

    public function postTweet(Status $status, string $socialText): string {
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
            Log::error('Legacy Tweet wasn\'t sent. Twitter API Response was not 200.', [
                'response' => [
                    'code' => $connection->getLastHttpCode(),
                    'body' => $connection->getLastBody(),
                ]
            ]);
            throw new TweetNotSendException($connection->getLastHttpCode());
        }

        return strval($response->id);
    }
}
