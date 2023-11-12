<?php

namespace App\Http\Controllers\Backend\Social;


use App\Enum\CacheKey;
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

    private function getData(): ?array {
        return Cache::remember(CacheKey::getMastodonProfileInformationKey($this->user), 60 * 60 /* 1 hour */, function() {
            return $this->fetchProfileInformation();
        });
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
                Log::warning("Unable to fetch mastodon information for user#{$this->user->id} for Mastodon-Server '{$mastodonServer->domain}' and mastodon_id#{$this->user->socialProfile->mastodon_id}");
                Log::warning($exception);
            }
        }

        return null;
    }
}
