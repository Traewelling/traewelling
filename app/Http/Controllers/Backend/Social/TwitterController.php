<?php

namespace App\Http\Controllers\Backend\Social;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Controller;
use App\Models\SocialLoginProfile;
use App\Models\Status;
use App\Models\User;
use App\Notifications\TwitterNotSent;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

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
        if ($sPro?->twitter_id === null || $sPro?->twitter_token === null || $sPro?->twitter_tokenSecret === null) {
            throw new NotConnectedException();
        }
        return new TwitterOAuth(
            consumerKey:      config('trwl.twitter_id'),
            consumerSecret:   config('trwl.twitter_secret'),
            oauthToken:       $user->socialProfile->twitter_token,
            oauthTokenSecret: $user->socialProfile->twitter_tokenSecret
        );
    }

    /**
     * Function to create a user with a login-provider.
     * If logged in, the user will have the login-provider added.
     * If a user with corresponding login-provider already exists, it will be returned.
     *
     * @param SocialiteUser $socialiteUser
     *
     * @return User model
     */
    public static function getUserFromSocialite(SocialiteUser $socialiteUser): User {
        $socialProfile = SocialLoginProfile::where('twitter_id', $socialiteUser->id)->first();

        if ($socialProfile !== null) {
            self::updateToken($socialProfile->user, $socialiteUser);
            return $socialProfile->user;
        }

        if (auth()->check()) {
            self::updateToken(auth()->user(), $socialiteUser);
            return auth()->user();
        }
        return self::createUser($socialiteUser);
    }

    private static function createUser(SocialiteUser $socialiteUser): User {
        $user = User::create([
                                 'name'     => SocialController::getDisplayName($socialiteUser),
                                 'username' => SocialController::getUniqueUsername($socialiteUser->getNickname()),
                             ]);
        self::updateToken($user, $socialiteUser);
        return $user;
    }

    private static function updateToken(User $user, SocialiteUser $socialiteUser): void {
        $user->socialProfile->update([
                                         'twitter_id'          => $socialiteUser->id,
                                         'twitter_token'       => $socialiteUser->token,
                                         'twitter_tokenSecret' => $socialiteUser->tokenSecret,
                                     ]);
    }

    /**
     * @param Status $status
     *
     * @throws NotConnectedException
     */
    public static function postStatus(Status $status): void {
        if ($status?->user?->socialProfile?->twitter_id === null || config('trwl.post_social') !== true) {
            return;
        }

        try {
            $connection = self::getApi($status->user);
            #dbl only works on Twitter.
            $socialText = $status->socialText;
            if ($status->user->always_dbl) {
                $socialText .= " #dbl";
            }
            $socialText .= ' ' . url("/status/{$status->id}");
            $connection->post("statuses/update",
                              [
                                  "status" => $socialText,
                                  'lat'    => $status->trainCheckin->Origin->latitude,
                                  'lon'    => $status->trainCheckin->Origin->longitude
                              ]
            );

            if ($connection->getLastHttpCode() !== 200) {
                $status->user->notify(new TwitterNotSent($connection->getLastHttpCode(), $status));
            }
            Log::info("Posted on Twitter: " . $socialText);
        } catch (NotConnectedException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            Log::error($exception);
            // The Twitter adapter itself won't throw Exceptions, but rather return HTTP codes.
            // However, we still want to continue if it explodes, thus why not catch exceptions here.
        }
    }
}
