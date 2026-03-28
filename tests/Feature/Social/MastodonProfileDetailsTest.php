<?php

declare(strict_types=1);

namespace Tests\Feature\Social;

use App\Http\Controllers\Backend\Social\MastodonProfileDetails;
use App\Models\MastodonServer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Revolution\Mastodon\Facades\Mastodon;
use Tests\FeatureTestCase;

class MastodonProfileDetailsTest extends FeatureTestCase
{
    use RefreshDatabase;

    private function makeServerAndProfile(User $user, string $domain, ?string $username): MastodonServer
    {
        $server = MastodonServer::create([
            'domain' => $domain,
            'client_id' => 'client',
            'client_secret' => 'secret',
        ]);
        $user->socialProfile->update([
            'mastodon_id' => 42,
            'mastodon_username' => $username,
            'mastodon_server' => $server->id,
            'mastodon_token' => 'token',
        ]);

        return $server;
    }

    public function test_get_profile_url_constructs_url_from_stored_username_without_api_call(): void
    {
        $user = User::factory()->create();
        $this->makeServerAndProfile($user, 'https://gotosocial.example', 'janedoe');

        $url = new MastodonProfileDetails(User::find($user->id))->getProfileUrl();

        $this->assertEquals('https://gotosocial.example/@janedoe', $url);
    }

    public function test_get_profile_url_strips_trailing_slash_from_domain(): void
    {
        $user = User::factory()->create();
        $this->makeServerAndProfile($user, 'https://mastodon.social/', 'johndoe');

        $url = new MastodonProfileDetails(User::find($user->id))->getProfileUrl();

        $this->assertEquals('https://mastodon.social/@johndoe', $url);
    }

    public function test_get_profile_url_falls_back_to_api_call_when_username_not_stored(): void
    {
        $user = User::factory()->create();
        $server = $this->makeServerAndProfile($user, 'https://mastodon.social', null);

        Mastodon::shouldReceive('domain')->with('https://mastodon.social')->andReturnSelf();
        Mastodon::shouldReceive('token')->with('token')->andReturnSelf();
        Mastodon::shouldReceive('call')
            ->once()
            ->andReturn(['url' => 'https://mastodon.social/@fallbackuser', 'username' => 'fallbackuser']);

        $url = new MastodonProfileDetails(User::find($user->id))->getProfileUrl();

        $this->assertEquals('https://mastodon.social/@fallbackuser', $url);
    }

    public function test_get_username_returns_stored_username_without_api_call(): void
    {
        $user = User::factory()->create();
        $this->makeServerAndProfile($user, 'https://mastodon.social', 'storeduser');

        $username = new MastodonProfileDetails(User::find($user->id))->getUserName();

        $this->assertEquals('storeduser', $username);
    }

    public function test_get_profile_host_is_derived_from_constructed_url(): void
    {
        $user = User::factory()->create();
        $this->makeServerAndProfile($user, 'https://gotosocial.example', 'alice');

        $host = new MastodonProfileDetails(User::find($user->id))->getProfileHost();

        $this->assertEquals('gotosocial.example', $host);
    }

    public function test_get_profile_url_returns_null_when_no_mastodon_connected(): void
    {
        $user = User::factory()->create();
        // socialProfile has no mastodon data by default

        $url = new MastodonProfileDetails(User::find($user->id))->getProfileUrl();

        $this->assertNull($url);
    }
}
