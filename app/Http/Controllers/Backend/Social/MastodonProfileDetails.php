<?php

namespace App\Http\Controllers\Backend\Social;


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
        return Cache::remember('mastodon_'.$this->user->username, 6000, function () {
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
                                                   ->get("/accounts/" . $this->user->socialProfile->mastodon_id);
                }
            } catch (Exception $exception) {
                // The connection might be broken, or the instance is down, or $user has removed the api rights
                // but has not told us yet.
                Log::warning($exception);
            }
        }

        return null;
    }
}
