<?php

declare(strict_types=1);

namespace App\Services\Mastodon;

use App\Exceptions\Mastodon\NoAvatarException;
use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Backend\Social\MastodonController as MastodonBackend;
use App\Models\MastodonServer;
use App\Models\User;
use App\Services\ProfilePictureService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;
use Revolution\Mastodon\Facades\Mastodon;

readonly class AvatarImportService
{
    public function __construct(private ProfilePictureService $profilePictureService) {}

    /**
     * @throws NotConnectedException
     * @throws NoAvatarException
     * @throws GuzzleException
     */
    public function importFromMastodon(User $user): void
    {
        $socialProfile = $user->socialProfile;

        if ($socialProfile?->mastodon_server === null || $socialProfile?->mastodon_token === null) {
            throw new NotConnectedException();
        }

        $mastodonServer = MastodonServer::findCached($socialProfile->mastodon_server);

        $account = Mastodon::domain($mastodonServer->domain)
            ->token($socialProfile->mastodon_token)
            ->call('GET', '/accounts/verify_credentials', MastodonBackend::getRequestOptions());

        $avatarUrl = $account['avatar'] ?? null;

        if ($avatarUrl === null || str_contains($avatarUrl, 'missing.png')) {
            throw new NoAvatarException();
        }

        $response = Http::timeout(10)->get($avatarUrl);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to download avatar from Mastodon.');
        }

        $this->profilePictureService->update($user, $response->body());
    }
}
