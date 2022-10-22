<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Backend\Social\AbstractTwitterController;
use App\Models\Status;
use App\Models\User;
use Coderjerk\BirdElephant\BirdElephant;
use Coderjerk\BirdElephant\Compose\Tweet;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Smolblog\OAuth2\Client\Provider\Twitter;

class TwitterController extends AbstractTwitterController
{

    /**
     * @param User $user
     *
     * @return BirdElephant
     * @throws IdentityProviderException
     * @throws NotConnectedException
     */
    public static function getApi(User $user): BirdElephant {
        $socialProfile = $user->socialProfile;
        if ($socialProfile?->twitter_id === null || $socialProfile?->twitter_token === null || $socialProfile->twitter_refresh_token === null || $socialProfile->twitter_token_expires_at === null) {
            throw new NotConnectedException();
        }
        if (Date::now()->isAfter($socialProfile->twitter_token_expires_at)) {
            $provider = new Twitter([
                                        'clientId'     => config('trwl.twitter_id'),
                                        'clientSecret' => config('trwl.twitter_secret'),
                                        'redirectUri'  => config('trwl.twitter_redirect'),
                                    ]);

            $token = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $socialProfile->twitter_refresh_token
            ]);
            $socialProfile->update([
                              'twitter_token'            => $token->getToken(),
                              'twitter_refresh_token'    => $token->getRefreshToken(),
                              'twitter_token_expires_at' => Date::createFromTimestamp($token->getExpires())
                          ]);
            $access_token = $token->getToken();
            Log::info("Refreshed twitter access token for {$socialProfile->twitter_id}");
        } else {
            $access_token = $socialProfile->twitter_token;
        }

        $credentials = [
            'consumer_key'    => config('trwl.twitter_id'),
            'consumer_secret' => config('trwl.twitter_secret'),
            'auth_token'      => $access_token
        ];
        return new BirdElephant($credentials);
    }

    public function postTweet(Status $status, string $socialText): string {
        $sPro = $status->user->socialProfile;
        if ($sPro?->twitter_id === null || $sPro?->twitter_token === null) {
            throw new NotConnectedException();
        }

        $api = self::getApi($status->user);


        $tweet = (new Tweet)->text($socialText);

        return $api->tweets()->tweet($tweet)->data->id_str;
    }
}
