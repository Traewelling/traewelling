<?php

declare(strict_types=1);

namespace Tests\Feature\Social;

use App\Enum\MastodonVisibility;
use App\Jobs\PostStatusOnMastodon;
use App\Models\Checkin;
use App\Models\MastodonServer;
use App\Models\Status;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Revolution\Mastodon\Facades\Mastodon;
use Tests\FeatureTestCase;

class PostStatusOnMastodonTest extends FeatureTestCase
{
    use RefreshDatabase;

    private function setupStatusWithMastodon(): Status
    {
        $checkin = Checkin::factory()->create();
        $status = $checkin->status;

        $server = MastodonServer::create([
            'domain' => 'https://social.traewelling.de',
            'client_id' => 'test_client',
            'client_secret' => 'test_secret',
        ]);

        $status->user->socialProfile->update([
            'mastodon_id' => 42,
            'mastodon_server' => $server->id,
            'mastodon_token' => 'test_token',
            'mastodon_visibility' => MastodonVisibility::PUBLIC,
        ]);

        config(['trwl.post_social' => true]);

        return $status->fresh(['user.socialProfile']);
    }

    private function setupMastodonFacade(): void
    {
        Mastodon::shouldReceive('domain')->andReturnSelf();
        Mastodon::shouldReceive('token')->andReturnSelf();
    }

    public function test_permanent_error_401_does_not_rethrow(): void
    {
        Notification::fake();
        $status = $this->setupStatusWithMastodon();
        $this->setupMastodonFacade();
        Mastodon::shouldReceive('createStatus')->andThrow(
            new ClientException('Unauthorized', new Request('POST', '/api/v1/statuses'), new Response(401))
        );

        new PostStatusOnMastodon($status, false)->handle();

        $this->assertTrue(true);
    }

    public function test_permanent_error_404_does_not_rethrow(): void
    {
        Notification::fake();
        $status = $this->setupStatusWithMastodon();
        $this->setupMastodonFacade();
        Mastodon::shouldReceive('createStatus')->andThrow(
            new ClientException('Not Found', new Request('POST', '/api/v1/statuses'), new Response(404))
        );

        new PostStatusOnMastodon($status, false)->handle();

        $this->assertTrue(true);
    }

    public function test_permanent_error_410_does_not_rethrow(): void
    {
        Notification::fake();
        $status = $this->setupStatusWithMastodon();
        $this->setupMastodonFacade();
        Mastodon::shouldReceive('createStatus')->andThrow(
            new ClientException('Gone', new Request('POST', '/api/v1/statuses'), new Response(410))
        );

        new PostStatusOnMastodon($status, false)->handle();

        $this->assertTrue(true);
    }

    public function test_permanent_error_422_does_not_rethrow(): void
    {
        Notification::fake();
        $status = $this->setupStatusWithMastodon();
        $this->setupMastodonFacade();
        Mastodon::shouldReceive('createStatus')->andThrow(
            new ClientException('Unprocessable', new Request('POST', '/api/v1/statuses'), new Response(422))
        );

        new PostStatusOnMastodon($status, false)->handle();

        $this->assertTrue(true);
    }

    public function test_temporary_server_error_rethrows_for_retry(): void
    {
        Notification::fake();
        $status = $this->setupStatusWithMastodon();
        $this->setupMastodonFacade();
        Mastodon::shouldReceive('createStatus')->andThrow(
            new ServerException('Internal Server Error', new Request('POST', '/api/v1/statuses'), new Response(500))
        );

        $this->expectException(ServerException::class);

        new PostStatusOnMastodon($status, false)->handle();
    }

    public function test_connection_error_rethrows_for_retry(): void
    {
        Notification::fake();
        $status = $this->setupStatusWithMastodon();
        $this->setupMastodonFacade();
        Mastodon::shouldReceive('createStatus')->andThrow(
            new ConnectException('Connection refused', new Request('POST', '/api/v1/statuses'))
        );

        $this->expectException(ConnectException::class);

        new PostStatusOnMastodon($status, false)->handle();
    }
}
