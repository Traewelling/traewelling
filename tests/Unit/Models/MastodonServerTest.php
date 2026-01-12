<?php

namespace Tests\Unit\Models;

use App\Models\MastodonServer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MastodonServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_cached_returns_server_from_cache(): void {
        $server = MastodonServer::create([
                                             'domain'        => 'mastodon.example',
                                             'client_id'     => 'test_id',
                                             'client_secret' => 'test_secret',
                                         ]);

        // First call should query database and cache result
        $foundServer = MastodonServer::findCached($server->id);
        $this->assertNotNull($foundServer);
        $this->assertEquals('mastodon.example', $foundServer->domain);

        // Verify cache exists
        $this->assertTrue(Cache::has("mastodon_server_{$server->id}"));

        // Delete from database to verify next call uses cache
        $serverId = $server->id;
        MastodonServer::where('id', $serverId)->forceDelete();

        // Second call should still return from cache
        $cachedServer = MastodonServer::findCached($serverId);
        $this->assertNotNull($cachedServer);
        $this->assertEquals('mastodon.example', $cachedServer->domain);
    }

    public function test_find_by_domain_cached_returns_server_from_cache(): void {
        $server = MastodonServer::create([
                                             'domain'        => 'social.example',
                                             'client_id'     => 'test_id',
                                             'client_secret' => 'test_secret',
                                         ]);

        // First call should query database and cache result
        $foundServer = MastodonServer::findByDomainCached('social.example');
        $this->assertNotNull($foundServer);
        $this->assertEquals('social.example', $foundServer->domain);

        // Verify cache exists
        $this->assertTrue(Cache::has("mastodon_server_domain_social.example"));

        // Delete from database to verify next call uses cache
        MastodonServer::where('id', $server->id)->forceDelete();

        // Second call should still return from cache
        $cachedServer = MastodonServer::findByDomainCached('social.example');
        $this->assertNotNull($cachedServer);
        $this->assertEquals('social.example', $cachedServer->domain);
    }

    public function test_save_clears_cache(): void {
        $server = MastodonServer::create([
                                             'domain'        => 'mastodon.example',
                                             'client_id'     => 'test_id',
                                             'client_secret' => 'test_secret',
                                         ]);

        // Cache the server
        MastodonServer::findCached($server->id);
        MastodonServer::findByDomainCached('mastodon.example');

        // Verify cache exists
        $this->assertTrue(Cache::has("mastodon_server_{$server->id}"));
        $this->assertTrue(Cache::has("mastodon_server_domain_mastodon.example"));

        // Update server (should clear cache)
        $server->client_id = 'new_id';
        $server->save();

        // Verify cache was cleared
        $this->assertFalse(Cache::has("mastodon_server_{$server->id}"));
        $this->assertFalse(Cache::has("mastodon_server_domain_mastodon.example"));
    }

    public function test_delete_clears_cache(): void {
        $server = MastodonServer::create([
                                             'domain'        => 'mastodon.example',
                                             'client_id'     => 'test_id',
                                             'client_secret' => 'test_secret',
                                         ]);

        $serverId = $server->id;

        // Cache the server
        MastodonServer::findCached($serverId);
        MastodonServer::findByDomainCached('mastodon.example');

        // Verify cache exists
        $this->assertTrue(Cache::has("mastodon_server_{$serverId}"));
        $this->assertTrue(Cache::has("mastodon_server_domain_mastodon.example"));

        // Delete server (should clear cache)
        $server->delete();

        // Verify cache was cleared
        $this->assertFalse(Cache::has("mastodon_server_{$serverId}"));
        $this->assertFalse(Cache::has("mastodon_server_domain_mastodon.example"));
    }

    public function test_find_cached_returns_null_for_nonexistent_server(): void {
        $foundServer = MastodonServer::findCached(99999);
        $this->assertNull($foundServer);
    }

    public function test_find_by_domain_cached_returns_null_for_nonexistent_domain(): void {
        $foundServer = MastodonServer::findByDomainCached('nonexistent.example');
        $this->assertNull($foundServer);
    }

    public function test_clear_cache_method_clears_both_caches(): void {
        $server = MastodonServer::create([
                                             'domain'        => 'mastodon.example',
                                             'client_id'     => 'test_id',
                                             'client_secret' => 'test_secret',
                                         ]);

        // Populate both caches
        MastodonServer::findCached($server->id);
        MastodonServer::findByDomainCached('mastodon.example');

        // Verify caches exist
        $this->assertTrue(Cache::has("mastodon_server_{$server->id}"));
        $this->assertTrue(Cache::has("mastodon_server_domain_mastodon.example"));

        // Call clearCache
        $server->clearCache();

        // Verify both caches were cleared
        $this->assertFalse(Cache::has("mastodon_server_{$server->id}"));
        $this->assertFalse(Cache::has("mastodon_server_domain_mastodon.example"));
    }
}
