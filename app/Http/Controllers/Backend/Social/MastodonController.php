<?php

namespace App\Http\Controllers\Backend\Social;

use App\Exceptions\SocialAuth\InvalidMastodonException;
use App\Http\Controllers\Backend\Helper\StatusHelper;
use App\Http\Controllers\Controller;
use App\Models\MastodonServer;
use App\Models\SocialLoginProfile;
use App\Models\Status;
use App\Models\User;
use App\Notifications\MastodonNotSent;
use App\Services\MastodonDomainExtractionService;
use Error;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
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
        $domain = (new MastodonDomainExtractionService())->formatDomain($domain);

        $mastodonServer = MastodonServer::where('domain', $domain)->first();

        //If we ever run into a reset of Mastodon AppKeys (#), then this recreates the keys.
        //Keys have to be set to 0 in the database, since the fields are covered by NOT NULL constraint
        if ($mastodonServer?->client_id <= 1 || $mastodonServer?->client_secret <= 1) {
            return self::createMastodonServer($domain);
        }

        return $mastodonServer ?? self::createMastodonServer($domain);
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
                client_name:   config('services.mastodon.client_name'), //TODO: why is client name required here?
                redirect_uris: config('services.mastodon.redirect'),
                scopes:        'write read',
                website:       config('app.url')
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

    /**
     * See: https://docs.joinmastodon.org/methods/statuses/#create
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public static function postStatus(Status $status, bool $shouldPostAsChain = false): void {
        if (config('trwl.post_social') !== true) {
            Log::error("Was dispatched to post on Mastodon, but POST_SOCIAL env variable is not set.");
            return;
        }

        if ($status->user?->socialProfile?->mastodon_server === null) {
            return;
        }

        $traverseChain = null;
        if ($shouldPostAsChain) {
            $chainHead = self::getLastSavedPostIdFromUserStatuses($status->user);

            if ($chainHead !== null) {
                $traverseChain = self::getEndOfChain($status->user, $chainHead->mastodon_post_id);
            }
        }

        try {
            $statusText     = StatusHelper::getSocialText($status, true);
            $statusText     .= ' ' . url("/status/{$status->id}");
            $mastodonDomain = MastodonServer::find($status->user->socialProfile->mastodon_server)->domain;
            Mastodon::domain($mastodonDomain)->token($status->user->socialProfile->mastodon_token);

            $postResponse = Mastodon::createStatus($statusText, [
                'visibility'     => strtolower($status->user->socialProfile->mastodon_visibility->name),
                'in_reply_to_id' => $traverseChain
            ]);

            $status->update(['mastodon_post_id' => $postResponse['id']]);
            Log::info("Posted on Mastodon (domain=" . $mastodonDomain . "): " . $statusText);
        } catch (GuzzleException $e) {
            $status->user->notify(new MastodonNotSent($e->getCode(), $status));
            throw $e;
        } catch (Exception|Error $e) {
            $status->user->notify(new MastodonNotSent(0, $status));
            Log::error($e);
            throw $e;
        }
    }

    /**
     * @param User   $user           Trwl user with social profile
     * @param string $mastodonPostId Id of a known post of the chain, taken from the statuses table.
     *
     * @return string|null The id of the last post that can be replied to. Returns null if there is none.
     * @see https://docs.joinmastodon.org/entities/Context/ Documentation of the Mastodon Context API.
     * @see https://docs.joinmastodon.org/methods/statuses/ Documentation of the Mastodon Status API.
     */
    public static function getEndOfChain(User $user, string $mastodonPostId): ?string {
        $client = self::getClient($user);

        try {
            $context = $client->call("GET", "/statuses/{$mastodonPostId}/context", options: self::getRequestOptions());
        } catch (GuzzleException $e) {
            Log::info("Unable to chain toot because of an issue with the connecting mastodon server.");
            if ($e->getCode() == 404) {
                Log::info("Original Post seems to be deleted.");
            }
            report($e);
            return null;
        }

        // Mastodon transmits ids as strings
        // and since we want to use === whenever possible, we convert the mastodon_id to a string.
        $mastodonUserId = (string) $user->socialProfile->mastodon_id;
        $onlyThread     = array_filter($context['descendants'], function($toot) use ($mastodonUserId): bool {
            return
                // We never want to interact with any direct messages
                $toot['visibility'] !== 'direct'

                // Only take posts that are from $OP.
                && $toot['account']['id'] === $mastodonUserId

                // Only take posts that are direct replies to a post by OP, discarding posts from OP that don't
                // contribute to the original thread.
                && (!isset($toot['in_reply_to_account_id']) || $toot['in_reply_to_account_id'] === $mastodonUserId);
        });

        // If there is no post left, resort to the original post.
        if (empty($onlyThread)) {
            return $mastodonPostId;
        }

        // Take the newest item of the thread.
        return last($onlyThread)['id'];
    }

    private static function getClient(User $user) {
        $mastodonDomain = MastodonServer::find($user->socialProfile->mastodon_server)->domain;
        return Mastodon::domain($mastodonDomain)->token($user->socialProfile->mastodon_token);
    }

    public static function getLastSavedPostIdFromUserStatuses(User $user) {
        return $user
            ->statuses()
            ->whereNotNull('mastodon_post_id')
            ->latest()
            ->first();
    }

    public static function getRequestOptions(): array {
        return [RequestOptions::TIMEOUT => config('services.mastodon.timeout')];
    }
}
