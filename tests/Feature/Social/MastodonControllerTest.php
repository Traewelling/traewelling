<?php

declare(strict_types=1);

namespace Tests\Feature\Social;

use App\Enum\MastodonVisibility;
use App\Exceptions\SocialAuth\InvalidMastodonException;
use App\Http\Controllers\Backend\Social\MastodonController;
use App\Models\Checkin;
use App\Models\MastodonServer;
use App\Models\Status;
use App\Models\User;
use App\Notifications\MastodonNotSent;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Two\User as SocialiteUser;
use Revolution\Mastodon\Facades\Mastodon;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\FeatureTestCase;

class MastodonControllerTest extends FeatureTestCase
{
    use RefreshDatabase;

    const USERID_OP = '2342';

    const USERID_ANSWER = '2343';

    const TOOTID_OP = '1337';

    const TOOTID_ANSWER = '1338';

    const TOOTID_ANSWER_2 = '1339';

    const TOOTID_ANSWER_3 = '1340';

    const OP_CONTEXT_URL = '/statuses/' . self::TOOTID_OP . '/context';

    public function test_find_end_of_chain_if_there_are_no_answers(): void
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post <== THIS ONE
         * - end.
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andReturn(['descendants' => []]);

        $this->assertEquals(self::TOOTID_OP, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_find_end_of_chain_if_there_is_one_answer_from_other_person(): void
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post <== THIS ONE
         * - toodid-answer from userid-answer
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andReturn(
                [
                    'descendants' => [
                        [
                            'id' => self::TOOTID_ANSWER,
                            'in_reply_to_id' => self::TOOTID_OP,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'unlisted',
                            'account' => ['id' => self::USERID_ANSWER],
                            'mentions' => [['id' => self::USERID_OP]],
                        ],
                    ],
                ]
            );

        $this->assertEquals(self::TOOTID_OP, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_find_end_of_chain_if_there_is_a_conversation_with_another_person(): void
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post <== THIS ONE
         * - toodid-answer from userid-answer
         *   - tootid-answer2 from userid-op
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andReturn(
                [
                    'descendants' => [
                        [
                            'id' => self::TOOTID_ANSWER,
                            'in_reply_to_id' => self::TOOTID_OP,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'unlisted',
                            'account' => ['id' => self::USERID_ANSWER],
                            'mentions' => [['id' => self::USERID_OP]],
                        ],
                        [
                            'id' => self::TOOTID_ANSWER_2,
                            'in_reply_to_id' => self::TOOTID_ANSWER,
                            'in_reply_to_account_id' => self::USERID_ANSWER,
                            'visibility' => 'unlisted',
                            'account' => ['id' => self::USERID_OP],
                            'mentions' => [['id' => self::USERID_ANSWER]],
                        ],
                    ],
                ]
            );

        $this->assertEquals(self::TOOTID_OP, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_find_end_of_chain_if_there_is_a_thread_with_one_post(): void
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post
         * - tootid-answer from userid-op <== THIS ONE
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andReturn(
                [
                    'descendants' => [
                        [
                            'id' => self::TOOTID_ANSWER,
                            'in_reply_to_id' => self::TOOTID_OP,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'unlisted',
                            'account' => ['id' => self::USERID_OP],
                            'mentions' => [],
                        ],
                    ],
                ]
            );

        $this->assertEquals(self::TOOTID_ANSWER, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_find_end_of_chain_if_there_is_a_thread_with_two_posts_and_someone_is_mentioned_in_the_second_post(): void
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post
         * - tootid-answer from userid-op with mention of userid-bob <== THIS ONE
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andReturn(
                [
                    'descendants' => [
                        [
                            'id' => self::TOOTID_ANSWER,
                            'in_reply_to_id' => self::TOOTID_OP,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'unlisted',
                            'account' => ['id' => self::USERID_OP],
                            'mentions' => [['id' => self::USERID_ANSWER]],
                        ],
                    ],
                ]
            );

        $this->assertEquals(self::TOOTID_ANSWER, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_find_end_of_chain_if_there_is_a_thread_with_two_posts_and_someone_is_mentioned_in_the_second_post_and_that_post_is_a_direct_message(): void
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post <== THIS ONE
         * - tootid-answer from userid-op with mention of userid-bob which is a DM
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andReturn(
                [
                    'descendants' => [
                        [
                            'id' => self::TOOTID_ANSWER,
                            'in_reply_to_id' => self::TOOTID_OP,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'direct',
                            'account' => ['id' => self::USERID_OP],
                            'mentions' => [['id' => self::USERID_ANSWER]],
                        ],
                    ],
                ]
            );

        $this->assertEquals(self::TOOTID_OP, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_find_end_of_chain_if_there_is_a_thread_with_multiple_posts(): void
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post
         * - tootid-answer from userid-op
         *   - tootid-answer2 from userid-op <== THIS ONE
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andReturn(
                [
                    'descendants' => [
                        [
                            'id' => self::TOOTID_ANSWER,
                            'created_at' => '2022-11-25T23:27:49.031Z',
                            'in_reply_to_id' => self::TOOTID_OP,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'unlisted',
                            'content' => '<p>Thread 1</p>',
                            'account' => ['id' => self::USERID_OP],
                        ],
                        [
                            'id' => self::TOOTID_ANSWER_2,
                            'created_at' => '2022-11-26T00:28:07.686Z',
                            'in_reply_to_id' => self::TOOTID_ANSWER,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'unlisted',
                            'content' => '<p>Thread 2</p>',
                            'account' => ['id' => self::USERID_OP],
                        ],
                    ],
                ]
            );

        $this->assertEquals(self::TOOTID_ANSWER_2, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_find_end_of_chain_if_there_is_a_thread_with_multiple_posts_and_some_answers(): void
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post
         * - tootid-answer from userid-op
         *   - tootid-answer2 from userid-op <== THIS ONE
         *   - tootid-answer3 from userid-answer
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andReturn(
                [
                    'descendants' => [
                        [
                            'id' => self::TOOTID_ANSWER,
                            'created_at' => '2022-11-25T23:27:49.031Z',
                            'in_reply_to_id' => self::TOOTID_OP,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'unlisted',
                            'content' => '<p>2/3</p>',
                            'account' => ['id' => self::USERID_OP],
                        ],
                        [
                            'id' => self::TOOTID_ANSWER_2,
                            'created_at' => '2022-11-26T00:28:07.686Z',
                            'in_reply_to_id' => self::TOOTID_ANSWER,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'unlisted',
                            'content' => '<p>3/3</p>',
                            'account' => ['id' => self::USERID_OP],
                        ],
                        [
                            'id' => self::TOOTID_ANSWER_3,
                            'created_at' => '2022-11-25T22:28:33.000Z',
                            'in_reply_to_id' => self::TOOTID_ANSWER,
                            'in_reply_to_account_id' => self::USERID_OP,
                            'visibility' => 'unlisted',
                            'content' => '<p>Answer on 2/3 in thread</p>',
                            'account' => ['id' => self::USERID_ANSWER],
                            'mentions' => [['id' => self::USERID_OP]],
                        ],
                    ],
                ]
            );

        $this->assertEquals(self::TOOTID_ANSWER_2, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_find_end_of_chain_if_original_post_is_not_found()
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post is deleted.
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andThrowExceptions([new ClientException(
                '{"error":"Record not found"}',
                new Request('GET', self::OP_CONTEXT_URL),
                new Response(404))]);

        Log::shouldReceive('error')->once();
        Log::shouldReceive('info')->twice();

        $this->assertNull(MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_find_end_of_chain_if_mastodon_server_unreachable()
    {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post is deleted.
         */
        Mastodon::shouldReceive('call')
            ->once()
            ->with('GET', self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
            ->andThrowExceptions([new ConnectException('server not available',
                new Request('GET', self::OP_CONTEXT_URL)
            )]);

        Log::shouldReceive('error')->once();
        Log::shouldReceive('info')->once();

        $this->assertNull(MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function test_post_status_returns_early_when_post_social_disabled(): void
    {
        config(['trwl.post_social' => false]);
        $status = Status::factory()->create();

        MastodonController::postStatus($status);

        $this->assertNull($status->fresh()->mastodon_post_id);
    }

    public function test_post_status_returns_early_when_user_has_no_mastodon_server(): void
    {
        config(['trwl.post_social' => true]);
        $status = Status::factory()->create();
        // socialProfile.mastodon_server is null by default

        MastodonController::postStatus($status);

        $this->assertNull($status->fresh()->mastodon_post_id);
    }

    public function test_post_status_saves_mastodon_post_id_on_success(): void
    {
        config(['trwl.post_social' => true]);
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

        Mastodon::shouldReceive('domain')->with('https://social.traewelling.de')->andReturnSelf();
        Mastodon::shouldReceive('token')->with('test_token')->andReturnSelf();
        Mastodon::shouldReceive('createStatus')->andReturn(['id' => '99999']);

        MastodonController::postStatus($status->fresh('user.socialProfile'));

        $this->assertEquals('99999', $status->fresh()->mastodon_post_id);
    }

    public function test_post_status_notifies_user_and_rethrows_guzzle_exception(): void
    {
        Notification::fake();
        config(['trwl.post_social' => true]);
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
        $user = $status->user;

        Mastodon::shouldReceive('domain')->andReturnSelf();
        Mastodon::shouldReceive('token')->andReturnSelf();
        Mastodon::shouldReceive('createStatus')->andThrow(
            new ClientException('Unauthorized', new Request('POST', '/api/v1/statuses'), new Response(401))
        );

        try {
            MastodonController::postStatus($status->fresh('user.socialProfile'));
            $this->fail('Expected ClientException');
        } catch (ClientException) {
            Notification::assertSentTo($user, MastodonNotSent::class);
        }
    }

    public function test_get_last_saved_post_id_returns_null_when_no_mastodon_posts(): void
    {
        $user = User::factory()->create();
        Status::factory()->create(['user_id' => $user->id, 'mastodon_post_id' => null]);

        $result = MastodonController::getLastSavedPostIdFromUserStatuses($user);

        $this->assertNull($result);
    }

    public function test_get_last_saved_post_id_returns_latest_post(): void
    {
        $user = User::factory()->create();
        Status::factory()->create(['user_id' => $user->id, 'mastodon_post_id' => 'old_post']);
        $latest = Status::factory()->create(['user_id' => $user->id, 'mastodon_post_id' => 'latest_post']);

        $result = MastodonController::getLastSavedPostIdFromUserStatuses($user);

        $this->assertNotNull($result);
        $this->assertEquals($latest->id, $result->id);
        $this->assertEquals('latest_post', $result->mastodon_post_id);
    }

    public function test_get_mastodon_server_returns_existing_server(): void
    {
        $server = MastodonServer::create([
            'domain' => 'https://social.traewelling.de',
            'client_id' => 'valid_client',
            'client_secret' => 'valid_secret',
        ]);

        $result = MastodonController::getMastodonServer('social.traewelling.de');

        $this->assertEquals($server->id, $result->id);
    }

    public function test_get_mastodon_server_recreates_server_with_invalid_credentials(): void
    {
        config([
            'services.mastodon.client_name' => 'TraewellingTest',
            'services.mastodon.redirect' => 'https://traewelling.test/oauth/mastodon/callback',
        ]);
        MastodonServer::create([
            'domain' => 'https://social.traewelling.de',
            'client_id' => '0',
            'client_secret' => '0',
        ]);

        Mastodon::shouldReceive('domain')->with('https://social.traewelling.de')->andReturnSelf();
        Mastodon::shouldReceive('createApp')->andReturn([
            'client_id' => 'new_client_id',
            'client_secret' => 'new_client_secret',
        ]);

        $result = MastodonController::getMastodonServer('social.traewelling.de');

        $this->assertEquals('new_client_id', $result->client_id);
        $this->assertEquals('new_client_secret', $result->client_secret);
    }

    public function test_get_mastodon_server_creates_new_server_for_unknown_domain(): void
    {
        config([
            'services.mastodon.client_name' => 'TraewellingTest',
            'services.mastodon.redirect' => 'https://traewelling.test/oauth/mastodon/callback',
        ]);

        Mastodon::shouldReceive('domain')->with('https://new.social.traewelling.de')->andReturnSelf();
        Mastodon::shouldReceive('createApp')->andReturn([
            'client_id' => 'created_client',
            'client_secret' => 'created_secret',
        ]);

        $result = MastodonController::getMastodonServer('new.social.traewelling.de');

        $this->assertNotNull($result);
        $this->assertEquals('https://new.social.traewelling.de', $result->domain);
        $this->assertEquals('created_client', $result->client_id);
    }

    public function test_get_mastodon_server_throws_for_unreachable_domain(): void
    {
        config([
            'services.mastodon.client_name' => 'TraewellingTest',
            'services.mastodon.redirect' => 'https://traewelling.test/oauth/mastodon/callback',
        ]);

        Mastodon::shouldReceive('domain')->andReturnSelf();
        Mastodon::shouldReceive('createApp')->andThrow(
            new ConnectException('Connection refused', new Request('POST', '/api/v1/apps'))
        );

        $this->expectException(InvalidMastodonException::class);

        MastodonController::getMastodonServer('offline.example');
    }

    private function makeSocialiteUser(string $id, string $token, string $name, string $nickname): SocialiteUser
    {
        $user = new SocialiteUser();
        $user->id = $id;
        $user->token = $token;
        $user->name = $name;
        $user->nickname = $nickname;

        return $user;
    }

    public function test_get_user_from_socialite_returns_existing_user_and_updates_token(): void
    {
        $user = User::factory()->create();
        $server = MastodonServer::create([
            'domain' => 'https://social.traewelling.de',
            'client_id' => 'client',
            'client_secret' => 'secret',
        ]);
        $user->socialProfile->update([
            'mastodon_id' => 12345,
            'mastodon_server' => $server->id,
            'mastodon_token' => 'old_token',
        ]);

        $socialiteUser = $this->makeSocialiteUser('12345', 'new_token', 'Test User', 'testuser');

        $result = MastodonController::getUserFromSocialite($socialiteUser, $server);

        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('new_token', $user->socialProfile->fresh()->mastodon_token);
    }

    public function test_get_user_from_socialite_links_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $server = MastodonServer::create([
            'domain' => 'https://social.traewelling.de',
            'client_id' => 'client',
            'client_secret' => 'secret',
        ]);

        $socialiteUser = $this->makeSocialiteUser('99999', 'my_token', 'Auth User', 'authuser');

        $result = MastodonController::getUserFromSocialite($socialiteUser, $server);

        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('my_token', $user->socialProfile->fresh()->mastodon_token);
        $this->assertEquals(99999, $user->socialProfile->fresh()->mastodon_id);
    }

    public function test_get_user_from_socialite_creates_new_user_when_registration_enabled(): void
    {
        config(['app.registration.enabled' => true]);

        $server = MastodonServer::create([
            'domain' => 'https://social.traewelling.de',
            'client_id' => 'client',
            'client_secret' => 'secret',
        ]);

        $socialiteUser = $this->makeSocialiteUser('77777', 'brand_new_token', 'New User', 'newuser');

        $result = MastodonController::getUserFromSocialite($socialiteUser, $server);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('users', ['username' => 'newuser']);
        $this->assertEquals('brand_new_token', $result->socialProfile->mastodon_token);
    }

    public function test_get_user_from_socialite_aborts_when_registration_disabled(): void
    {
        config(['app.registration.enabled' => false]);

        $server = MastodonServer::create([
            'domain' => 'https://social.traewelling.de',
            'client_id' => 'client',
            'client_secret' => 'secret',
        ]);

        $socialiteUser = $this->makeSocialiteUser('88888', 'some_token', 'Another User', 'anotheruser');

        $this->expectException(HttpException::class);

        MastodonController::getUserFromSocialite($socialiteUser, $server);
    }

    private function setupUserWithMastodonAccount(): User
    {
        $user = User::factory()->create();

        $mastodonServer = MastodonServer::create([
            'domain' => 'https://example.com',
            'client_id' => '123abc',
            'client_secret' => '123abc',
        ]);
        $socialProfile = $mastodonServer
            ->socialProfiles()
            ->create([
                'user_id' => $user->id,
                'mastodon_id' => (int) self::USERID_OP,
                'mastodon_server' => $mastodonServer->id,
                'mastodon_token' => 'my_mastodon_token',
            ]);
        $socialProfile->user()->associate($user);

        Mastodon::shouldReceive('domain')
            ->once()
            ->with('https://example.com')
            ->andReturnSelf();
        Mastodon::shouldReceive('token')
            ->once()
            ->with('my_mastodon_token')
            ->andReturnSelf();

        return $user;
    }
}
