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
use GuzzleHttp\Exception\GuzzleException;
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

        // remove leading usernames
        if (str_contains($domain, '@')) {
            $domain = last(explode('@', $domain));
        }

        // Force HTTPS
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
        } catch (GuzzleException $exception) {
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

    public static function postStatus(Status $status, bool $shouldPostAsChain = true): void {
        if (config('trwl.post_social') !== true) {
            Log::error("Was dispatched to post on Mastodon, but POST_SOCIAL env variable is not set.");
            return;
        }

        if ($status?->user?->socialProfile?->mastodon_server === null) {
            return;
        }

        $traverseChain = null;
        if ($shouldPostAsChain) {
            $chainHead = self::getLastSavedPostIdFromUserStatuses($status->user);

            if ($chainHead != null) {
                $traverseChain = self::getEndOfChain($status->user, $chainHead->mastodon_post_id);
            }
        }

        try {
            $statusText     = $status->socialText . ' ' . url("/status/{$status->id}");
            $mastodonDomain = MastodonServer::find($status->user->socialProfile->mastodon_server)->domain;
            Mastodon::domain($mastodonDomain)->token($status->user->socialProfile->mastodon_token);

            $post_response = Mastodon::createStatus($statusText, [
                'visibility'     => 'unlisted',
                'in_reply_to_id' => $traverseChain
            ]);

            $status->update(['mastodon_post_id' => $post_response['id']]);
            Log::info("Posted on Mastodon (domain=" . $mastodonDomain . "): " . $statusText);
        } catch (GuzzleException $e) {
            $status->user->notify(new MastodonNotSent($e->getCode(), $status));
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * @param User   $user             Trwl user with social profile
     * @param string $mastodon_post_id Id of a known post of the chain, taken from the statuses table.
     *
     * @return string|null The id of the last post that can be replied to. Returns null if there is none.
     * @see https://docs.joinmastodon.org/entities/Context/ Documentation of the Mastodon Context API.
     * @see https://docs.joinmastodon.org/methods/statuses/ Documentation of the Mastodon Status API.
     */
    public static function getEndOfChain(User $user, string $mastodon_post_id): ?string {
        $client = self::getClient($user);

        try {
            $context = $client->get('/statuses/' . $mastodon_post_id . '/context');
        } catch (GuzzleException $e) {
            Log::info("Unable to chain toot because of an issue with the connecting mastodon server.");
            if ($e->getCode() == 404) {
                Log::info("Original Post seems to be deleted.");
            }
            report($e);
            return null;
        }

        $mastodonUserId = $user->socialProfile->mastodon_id;
        $only_thread    = array_filter($context['descendants'], function($toot) use ($mastodonUserId): bool {
            return
                // We never want to interact with any direct messages
                $toot['visibility'] !== 'direct'

                // Only take posts that are from $OP.
                && $toot['account']['id'] == $mastodonUserId

                // Only take posts that are direct replies to an post by OP, discarding posts from OP that don't
                // contribute to the original thread.
                && (!isset($toot['in_reply_to_account_id']) || $toot['in_reply_to_account_id'] == $mastodonUserId);
        });

        // If there is no post left, resort to the original post.
        if (empty($only_thread)) {
            return $mastodon_post_id;
        }

        // Take the newest item of the thread.
        return last($only_thread)['id'];
    }

    private static function getClient(User $user) {
        $mastodonDomain = MastodonServer::find($user->socialProfile->mastodon_server)->domain;
        return Mastodon::domain($mastodonDomain)->token($user->socialProfile->mastodon_token);
    }

    private static function getLastSavedPostIdFromUserStatuses(User $user) {
        return $user
            ->statuses()
            ->whereNotNull('mastodon_post_id')
            ->latest()
            ->first();
    }
}
