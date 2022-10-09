<?php

namespace App\Http\Controllers\Backend\Social;

use App\Exceptions\SocialAuth\InvalidMastodonException;
use App\Http\Controllers\Controller;
use App\Models\MastodonServer;
use App\Models\SocialLoginProfile;
use App\Models\Status;
use App\Models\User;
use App\Notifications\MastodonNotSent;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Revolution\Mastodon\Facades\Mastodon;

abstract class MastodonController extends Controller
{
    /**
     * Function to create a user with a login-provider.
     * If logged in, the user will have the login-provider added.
     * If a user with corresponding login-provider already exists, it will be returned.
     *
     * @param SocialiteUser  $socialiteUser
     * @param MastodonServer $server
     *
     * @return User model
     */
    public static function getUserFromSocialite(SocialiteUser $socialiteUser, MastodonServer $server): User {
        $socialProfile = SocialLoginProfile::where('mastodon_id', $socialiteUser->id)
                                           ->where('mastodon_server', $server->id)
                                           ->first();

        if ($socialProfile !== null) {
            self::updateToken($socialProfile->user, $socialiteUser, $server);
            return $socialProfile->user;
        }

        if (auth()->check()) {
            self::updateToken(auth()->user(), $socialiteUser, $server);
            return auth()->user();
        }
        return self::createUser($socialiteUser, $server);
    }

    /**
     * @param string $domain
     *
     * @return MastodonServer|null
     * @throws InvalidMastodonException
     */
    public static function getMastodonServer(string $domain): ?MastodonServer {
        $domain = self::formatDomain($domain);

        $mastodonServer = MastodonServer::where('domain', $domain)->first();

        //If we ever run into a reset of Mastodon AppKeys (#), then this recreates the keys.
        //Keys have to be set to 0 in the database, since the fields are covered by NOT NULL constraint
        if ($mastodonServer?->client_id <= 1 || $mastodonServer?->client_secret <= 1) {
            return self::createMastodonServer($domain);
        }

        return $mastodonServer ?? self::createMastodonServer($domain);
    }

    public static function formatDomain(string $domain): string {
        $domain = strtolower($domain);
        $domain = str_replace('http://', 'https://', $domain);
        if (!str_starts_with($domain, 'https://')) {
            $domain = 'https://' . $domain;
        }
        return $domain;
    }

    /**
     * @param string $domain
     *
     * @return MastodonServer
     * @throws InvalidMastodonException
     */
    private static function createMastodonServer(string $domain): MastodonServer {
        try {
            $info = Mastodon::domain($domain)->createApp(
                client_name:   config('trwl.mastodon_appname'),
                redirect_uris: config('trwl.mastodon_redirect'),
                scopes:        'write read'
            );
            return MastodonServer::updateOrCreate([
                                                      'domain' => $domain,
                                                  ], [
                                                      'client_id'     => $info['client_id'],
                                                      'client_secret' => $info['client_secret'],
                                                  ]);
        } catch (ClientException $exception) {
            report($exception);
            throw new InvalidMastodonException();
        }
    }

    private static function createUser(SocialiteUser $socialiteUser, MastodonServer $server): User {
        $user = User::create([
                                 'name'     => SocialController::getDisplayName($socialiteUser),
                                 'username' => SocialController::getUniqueUsername($socialiteUser->getNickname()),
                             ]);
        self::updateToken($user, $socialiteUser, $server);
        return $user;
    }

    private static function updateToken(User $user, SocialiteUser $socialiteUser, MastodonServer $server): void {
        $user->socialProfile->update([
                                         'mastodon_id'     => $socialiteUser->id,
                                         'mastodon_token'  => $socialiteUser->token,
                                         'mastodon_server' => $server->id,
                                     ]);
    }

    public static function postStatus(Status $status): void {
        if ($status?->user?->socialProfile?->mastodon_server === null || config('trwl.post_social') !== true) {
            return;
        }

        try {
            $statusText     = $status->socialText . ' ' . url("/status/{$status->id}");
            $mastodonDomain = MastodonServer::find($status->user->socialProfile->mastodon_server)->domain;
            Mastodon::domain($mastodonDomain)->token($status->user->socialProfile->mastodon_token);
            Mastodon::createStatus($statusText, ['visibility' => 'unlisted']);
            Log::info("Posted on Mastodon (domain=" . $mastodonDomain . "): " . $statusText);
        } catch (RequestException $e) {
            $status->user->notify(new MastodonNotSent($e->getResponse()?->getStatusCode(), $status));
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
