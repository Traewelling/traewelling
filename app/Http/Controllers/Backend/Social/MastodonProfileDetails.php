<?php

namespace App\Http\Controllers\Backend\Social;

use App\Helpers\CacheKey;
use App\Models\MastodonServer;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Revolution\Mastodon\Facades\Mastodon;
use TypeError;

class MastodonProfileDetails
{
    private User $user;

    private bool $lastErrorWasTemporary = false;

    private const CACHE_TTL_SUCCESS = 3600;      // 1 hour for successful fetches

    private const CACHE_TTL_TEMPORARY_ERROR = 900;       // 15 minutes for temporary errors

    private const CACHE_TTL_PERMANENT_ERROR = 3600;      // 1 hour for permanent errors

    private const PERMANENT_ERROR_CODES = [401, 404, 410];

    private const TEMPORARY_ERROR_CODES = [408, 429, 500, 502, 503, 504];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getProfileUrl(): ?string
    {
        return $this->getData()['url'] ?? null;
    }

    public function getProfileHost(): ?string
    {
        return parse_url($this->getProfileUrl(), PHP_URL_HOST) ?? null;
    }

    public function getUserName(): ?string
    {
        return $this->getData()['username'] ?? null;
    }

    private function getData(): ?array
    {
        $cacheKey = CacheKey::getMastodonProfileInformationKey($this->user);

        if (Cache::has($cacheKey)) {
            Cache::forget(CacheKey::getMastodonProfileErrorKey($this->user));

            return Cache::get($cacheKey);
        }
        $data = $this->fetchProfileInformation();

        $ttl = $this->determineCacheTtl($data);
        Cache::put($cacheKey, $data, $ttl);

        return $data;
    }

    /**
     * Determine cache TTL based on fetch result
     */
    private function determineCacheTtl(?array $data): int
    {
        if ($data !== null) {
            // Success: cache for longer period
            return self::CACHE_TTL_SUCCESS;
        }

        if ($this->lastErrorWasTemporary) {
            // Temporary error: short cache to retry soon
            return self::CACHE_TTL_TEMPORARY_ERROR;
        }

        // Permanent error or no data: cache for longer to avoid repeated attempts
        return self::CACHE_TTL_PERMANENT_ERROR;
    }

    public function forgetData(): void
    {
        Cache::forget(CacheKey::getMastodonProfileInformationKey($this->user));
    }

    private function fetchProfileInformation(): ?array
    {
        if ($this->user?->socialProfile?->mastodon_token && $this->user->socialProfile?->mastodon_id) {
            try {
                $mastodonServer = MastodonServer::findCached($this->user->socialProfile->mastodon_server);
                if ($mastodonServer) {
                    return Mastodon::domain($mastodonServer->domain)
                        ->token($this->user->socialProfile->mastodon_token)
                        ->call(
                            method: 'GET',
                            api: '/accounts/' . $this->user->socialProfile->mastodon_id,
                            options: MastodonController::getRequestOptions()
                        );
                }
            } catch (Exception|TypeError $exception) {
                $this->handleFetchError($exception);
            }
        }

        return null;
    }

    /**
     * Handle errors from Mastodon API with proper classification
     */
    private function handleFetchError(Exception|TypeError $exception): void
    {
        $code = $exception->getCode();
        $mastodonServer = MastodonServer::findCached($this->user->socialProfile->mastodon_server);

        if (in_array($code, self::PERMANENT_ERROR_CODES)) {
            // Permanent errors: token invalid, account not found, or account deleted
            Log::warning(
                sprintf(
                    "Permanent Mastodon error (HTTP %d) for user#%d on server '%s' with mastodon_id#%d",
                    $code,
                    $this->user->id,
                    $mastodonServer?->domain ?? 'unknown',
                    $this->user->socialProfile->mastodon_id
                )
            );
            $this->lastErrorWasTemporary = false;
            Cache::increment(CacheKey::getMastodonProfileErrorKey($this->user));

            $errorCount = Cache::get(CacheKey::getMastodonProfileErrorKey($this->user), 0);
            if ($errorCount >= 48) { // e.g., 48 permanent errors ~ 2 days if checked every hour
                Log::info(sprintf(
                    'Mastodon error count for user#%d reached %d - removing mastodon information',
                    $this->user->id,
                    $errorCount
                ));

                $this->removeMastodonInformation();
            }
        } elseif (in_array($code, self::TEMPORARY_ERROR_CODES)) {
            // Temporary errors: timeouts, rate limits, server errors
            Log::info(
                sprintf(
                    "Temporary Mastodon error (HTTP %d) for user#%d on server '%s' - will retry later",
                    $code,
                    $this->user->id,
                    $mastodonServer?->domain ?? 'unknown'
                )
            );
            $this->lastErrorWasTemporary = true;
        } else {
            // Unknown error codes: treat as temporary and report for investigation
            Log::error(
                sprintf(
                    "Unknown Mastodon error (HTTP/Status %d) for user#%d on server '%s': %s",
                    $code,
                    $this->user->id,
                    $mastodonServer?->domain ?? 'unknown',
                    $exception->getMessage()
                )
            );
            $this->lastErrorWasTemporary = true;

            if (config('logging.level') === 'debug') {
                report($exception);
            }
        }
    }

    private function removeMastodonInformation(): void
    {
        if ($this->user->email_verified_at === null) {
            Log::info("User#{$this->user->id} has not verified his email address yet."
                      . ' Not removing mastodon information.');

            return;
        }
        Log::info("Removing mastodon information for user#{$this->user->id}");
        $this->user->socialProfile->update([
            'mastodon_id' => null,
            'mastodon_token' => null,
            'mastodon_server' => null,
            'mastodon_visibility' => 1,
        ]);
    }
}
