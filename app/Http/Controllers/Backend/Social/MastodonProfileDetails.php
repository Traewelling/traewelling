<?php

namespace App\Http\Controllers\Backend\Social;


use App\Helpers\CacheKey;
use App\Models\MastodonServer;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Revolution\Mastodon\Facades\Mastodon;

class MastodonProfileDetails
{
    private User $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function getProfileUrl(): ?string {
        return $this->getData()["url"] ?? null;
    }

    public function getProfileHost(): ?string {
        return parse_url($this->getProfileUrl(), PHP_URL_HOST) ?? null;
    }

    public function getUserName(): ?string {
        return $this->getData()["username"] ?? null;
    }

    private function getData(): ?array {
        return Cache::remember(CacheKey::getMastodonProfileInformationKey($this->user), 3600, function() {
            return $this->fetchProfileInformation();
        });
    }

    public function forgetData(): void {
        Cache::forget(CacheKey::getMastodonProfileInformationKey($this->user));
    }

    private function fetchProfileInformation(): ?array {
        if ($this->user?->socialProfile?->mastodon_token && $this->user->socialProfile?->mastodon_id) {
            try {
                $mastodonServer = MastodonServer::where('id', $this->user->socialProfile->mastodon_server)->first();
                if ($mastodonServer) {
                    return Mastodon::domain($mastodonServer->domain)
                                   ->token($this->user->socialProfile->mastodon_token)
                                   ->call(
                                       method:  "GET",
                                       api:     "/accounts/" . $this->user->socialProfile->mastodon_id,
                                       options: MastodonController::getRequestOptions()
                                   );
                }
            } catch (Exception $exception) {
                // The connection might be broken, or the instance is down, or $user has removed the api rights
                // but has not told us yet.
                Log::warning("Unable to fetch mastodon information for user#{$this->user->id} for Mastodon-Server '{$mastodonServer?->domain}' and mastodon_id#{$this->user->socialProfile->mastodon_id} (HTTP-Code: {$exception->getCode()})");
                if (in_array($exception->getCode(), [404, 410], true)) {
                    $this->removeMastodonInformation($exception->getCode());
                }
            }
        }

        return null;
    }

    private function removeMastodonInformation(int $reasonResponseCode = null): void {
        if ($this->user->email_verified_at === null) {
            Log::info("User#{$this->user->id} has not verified his email address yet."
                      . "Not removing mastodon information.");
            return;
        }
        activity()->performedOn($this->user)
                  ->log('Mastodon information removed' . ($reasonResponseCode !== null ? "due to response code $reasonResponseCode" : ''));
        $this->user->socialProfile->update([
                                               'mastodon_id'         => null,
                                               'mastodon_token'      => null,
                                               'mastodon_server'     => null,
                                               'mastodon_visibility' => 1,
                                           ]);
    }
}
