<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Backend\Social\AbstractTwitterController;
use App\Models\Status;
use App\Models\User;
use Coderjerk\BirdElephant\BirdElephant;
use Coderjerk\BirdElephant\Compose\Tweet;
use InvalidArgumentException;

class TwitterController extends AbstractTwitterController
{

    /**
     * @return BirdElephant
     * @throws InvalidArgumentException
     */
    public static function getApi(User $user): BirdElephant {
        $credentials = [
            'consumer_key'    => config('trwl.twitter_id'),
            'consumer_secret' => config('trwl.twitter_secret'),
            'auth_token'   => $user->socialProfile->twitter_token
        ];
        return new BirdElephant($credentials);
    }

    public function postTweet(Status $status, string $socialText): int {
        $sPro = $status->user->socialProfile;
        if ($sPro?->twitter_id === null || $sPro?->twitter_token === null) {
            throw new NotConnectedException();
        }

        $api = self::getApi($status->user);


        $tweet = (new Tweet)->text($socialText);

        return $api->tweets()->tweet($tweet)->data->id;
    }
}
