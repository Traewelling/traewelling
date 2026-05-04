<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Http\Controllers\Backend\Social\MastodonController;
use App\Models\MastodonServer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Revolution\Mastodon\Facades\Mastodon;
use Tests\ApiTestCase;

class ImportProfilePictureFromMastodonTest extends ApiTestCase
{
    use RefreshDatabase;

    private const MASTODON_TOKEN = 'test_token';

    private User $user;

    private MastodonServer $mastodonServer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->mastodonServer = MastodonServer::create([
            'domain' => 'https://mastodon.example',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
        ]);
        $this->user->socialProfile->update([
            'mastodon_server' => $this->mastodonServer->id,
            'mastodon_token' => self::MASTODON_TOKEN,
        ]);

        Passport::actingAs($this->user, ['*']);
    }

    public function test_import_profile_picture_from_mastodon(): void
    {
        $fakeAvatarUrl = 'https://mastodon.example/avatars/original/avatar.png';
        $fakeImageContent = file_get_contents(public_path('/img/user.png'));

        Mastodon::shouldReceive('domain')
            ->once()
            ->with('https://mastodon.example')
            ->andReturnSelf();
        Mastodon::shouldReceive('token')
            ->once()
            ->with(self::MASTODON_TOKEN)
            ->andReturnSelf();
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', '/accounts/verify_credentials', MastodonController::getRequestOptions())
            ->andReturn(['avatar' => $fakeAvatarUrl]);

        Http::fake([
            $fakeAvatarUrl => Http::response($fakeImageContent, 200),
        ]);

        $response = $this->postJson('/api/v1/settings/profile-picture/mastodon');

        $response->assertNoContent();
        $this->assertNotNull($this->user->fresh()->avatar);
    }

    public function test_import_fails_when_no_mastodon_account_connected(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/settings/profile-picture/mastodon');

        $response->assertNotFound();
    }

    public function test_import_fails_when_mastodon_account_has_no_avatar(): void
    {
        Mastodon::shouldReceive('domain')
            ->once()
            ->with('https://mastodon.example')
            ->andReturnSelf();
        Mastodon::shouldReceive('token')
            ->once()
            ->with(self::MASTODON_TOKEN)
            ->andReturnSelf();
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', '/accounts/verify_credentials', MastodonController::getRequestOptions())
            ->andReturn(['avatar' => 'https://mastodon.example/avatars/original/missing.png']);

        $response = $this->postJson('/api/v1/settings/profile-picture/mastodon');

        $response->assertStatus(400);
    }
}
