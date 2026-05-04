<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Mastodon;

use App\Exceptions\Mastodon\NoAvatarException;
use App\Exceptions\NotConnectedException;
use App\Http\Controllers\Backend\Social\MastodonController;
use App\Models\MastodonServer;
use App\Models\User;
use App\Services\Mastodon\AvatarImportService;
use App\Services\ProfilePictureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Revolution\Mastodon\Facades\Mastodon;
use Tests\Unit\UnitTestCase;

class AvatarImportServiceTest extends UnitTestCase
{
    use RefreshDatabase;

    private AvatarImportService $service;

    private ProfilePictureService $profilePictureService;

    private MastodonServer $mastodonServer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profilePictureService = Mockery::mock(ProfilePictureService::class);
        $this->service = new AvatarImportService($this->profilePictureService);

        $this->mastodonServer = MastodonServer::create([
            'domain' => 'https://mastodon.example',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
        ]);
    }

    public function test_throws_when_no_mastodon_connected(): void
    {
        $user = User::factory()->create();

        $this->expectException(NotConnectedException::class);

        $this->service->importFromMastodon($user);
    }

    public function test_throws_when_avatar_is_missing_placeholder(): void
    {
        $user = User::factory()->create();
        $user->socialProfile->update([
            'mastodon_server' => $this->mastodonServer->id,
            'mastodon_token' => 'token',
        ]);

        Mastodon::shouldReceive('domain')->once()->with('https://mastodon.example')->andReturnSelf();
        Mastodon::shouldReceive('token')->once()->with('token')->andReturnSelf();
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', '/accounts/verify_credentials', MastodonController::getRequestOptions())
            ->andReturn(['avatar' => 'https://mastodon.example/system/missing.png']);

        $this->expectException(NoAvatarException::class);

        $this->service->importFromMastodon($user);
    }

    public function test_imports_avatar_successfully(): void
    {
        $fakeImageContent = 'fake-image-bytes';
        $avatarUrl = 'https://mastodon.example/avatars/avatar.png';

        $user = User::factory()->create();
        $user->socialProfile->update([
            'mastodon_server' => $this->mastodonServer->id,
            'mastodon_token' => 'token',
        ]);

        Mastodon::shouldReceive('domain')->once()->with('https://mastodon.example')->andReturnSelf();
        Mastodon::shouldReceive('token')->once()->with('token')->andReturnSelf();
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', '/accounts/verify_credentials', MastodonController::getRequestOptions())
            ->andReturn(['avatar' => $avatarUrl]);

        Http::fake([$avatarUrl => Http::response($fakeImageContent, 200)]);

        $this->profilePictureService
            ->shouldReceive('update')
            ->once()
            ->with(Mockery::on(fn ($u) => $u->is($user)), $fakeImageContent);

        $this->service->importFromMastodon($user);
    }
}
