<?php

namespace App\Http\Controllers\Backend\Social;

use App\Exceptions\SocialAuth\InvalidMastodonException;
use App\Http\Controllers\Controller;
use App\Models\MastodonServer;
use App\Models\SocialLoginProfile;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Revolution\Mastodon\Facades\Mastodon;

abstract class MastodonController extends Controller
{
    /**
     * Function to create a user with a login-provider.
     * If logged in, the user will have the login-provider added.
     * If a user with corresponding login-provider already exists, it will be returned.
     *
     * @param $getInfo (response of Socialite->user())
     * @param $domain
     *
     * @return User|RedirectResponse|null model
     */
    public static function createUser(\Laravel\Socialite\Contracts\User $getInfo, $domain): User|RedirectResponse|null {
        $identifier = SocialLoginProfile::where('mastodon_id', $getInfo->id)
                                        ->where(
                                            'mastodon_server',
                                            MastodonServer::where('domain', $domain)->first()->id
                                        )
                                        ->first();

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

        $socialProfile                  = $user->socialProfile ?: new SocialLoginProfile;
        $socialProfile->mastodon_id     = $getInfo->id;
        $socialProfile->mastodon_token  = $getInfo->token;
        $socialProfile->mastodon_server = MastodonServer::where('domain', $domain)->first()->id;

        $user->socialProfile()->save($socialProfile);
        return $user;
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

    public static function formatDomain(string $domain) {
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
            $info = Mastodon::domain($domain)->createApp(config('trwl.mastodon_appname'), config('trwl.mastodon_redirect'), 'write read');
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
}
