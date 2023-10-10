<?php

namespace Tests\Feature\Social;

use App\Http\Controllers\Backend\Social\MastodonController;
use App\Models\MastodonServer;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Revolution\Mastodon\Facades\Mastodon;
use Tests\TestCase;

class MastodonControllerTest extends TestCase
{
    use RefreshDatabase;

    const USERID_OP       = "2342";
    const USERID_ANSWER   = "2343";
    const TOOTID_OP       = "1337";
    const TOOTID_ANSWER   = "1338";
    const TOOTID_ANSWER_2 = "1339";
    const TOOTID_ANSWER_3 = "1340";
    const OP_CONTEXT_URL  = '/statuses/' . self::TOOTID_OP . '/context';

    /**
     * @dataProvider providerTestFormatDomain
     */
    public function testFormatDomain($case, $expected): void {
        $formatted = MastodonController::formatDomain($case);
        $this->assertEquals($expected, $formatted);

        $validated = Validator::make(['domain' => $formatted], ['domain' => ['active_url']]);

        $this->assertFalse($validated->fails());
    }

    public static function providerTestFormatDomain(): array {
        return [
            ['https://uelfte.club', 'https://uelfte.club'],
            ['http://uelfte.club', 'https://uelfte.club'],
            ['uelfte.club', 'https://uelfte.club'],
            ['great_username@uelfte.club', 'https://uelfte.club'],
            ['@great_username@uelfte.club', 'https://uelfte.club'],
            ['https://mastodon.sergal.org', 'https://mastodon.sergal.org'] # see issue 1182
        ];
    }


    public function testFindEndOfChainIfThereAreNoAnswers(): void {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post <== THIS ONE
         * - end.
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andReturn(["descendants" => []]);

        $this->assertEquals(self::TOOTID_OP, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function testFindEndOfChainIfThereIsOneAnswerFromOtherPerson(): void {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post <== THIS ONE
         * - toodid-answer from userid-answer
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andReturn(
                    [
                        "descendants" => [
                            [
                                "id"                     => self::TOOTID_ANSWER,
                                "in_reply_to_id"         => self::TOOTID_OP,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "unlisted",
                                "account"                => ["id" => self::USERID_ANSWER],
                                "mentions"               => [["id" => self::USERID_OP]],
                            ],
                        ]
                    ]
                );

        $this->assertEquals(self::TOOTID_OP, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function testFindEndOfChainIfThereIsAConversationWithAnotherPerson(): void {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post <== THIS ONE
         * - toodid-answer from userid-answer
         *   - tootid-answer2 from userid-op
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andReturn(
                    [
                        "descendants" => [
                            [
                                "id"                     => self::TOOTID_ANSWER,
                                "in_reply_to_id"         => self::TOOTID_OP,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "unlisted",
                                "account"                => ["id" => self::USERID_ANSWER],
                                "mentions"               => [["id" => self::USERID_OP]],
                            ],
                            [
                                "id"                     => self::TOOTID_ANSWER_2,
                                "in_reply_to_id"         => self::TOOTID_ANSWER,
                                "in_reply_to_account_id" => self::USERID_ANSWER,
                                "visibility"             => "unlisted",
                                "account"                => ["id" => self::USERID_OP],
                                "mentions"               => [["id" => self::USERID_ANSWER]],
                            ],
                        ]
                    ]
                );

        $this->assertEquals(self::TOOTID_OP, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function testFindEndOfChainIfThereIsAThreadWithOnePost(): void {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post
         * - tootid-answer from userid-op <== THIS ONE
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andReturn(
                    [
                        "descendants" => [
                            [
                                "id"                     => self::TOOTID_ANSWER,
                                "in_reply_to_id"         => self::TOOTID_OP,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "unlisted",
                                "account"                => ["id" => self::USERID_OP],
                                "mentions"               => []
                            ]
                        ]
                    ]
                );

        $this->assertEquals(self::TOOTID_ANSWER, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function testFindEndOfChainIfThereIsAThreadWithTwoPostsAndSomeoneIsMentionedInTheSecondPost(): void {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post
         * - tootid-answer from userid-op with mention of userid-bob <== THIS ONE
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andReturn(
                    [
                        "descendants" => [
                            [
                                "id"                     => self::TOOTID_ANSWER,
                                "in_reply_to_id"         => self::TOOTID_OP,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "unlisted",
                                "account"                => ["id" => self::USERID_OP],
                                "mentions"               => [["id" => self::USERID_ANSWER]],
                            ]
                        ]
                    ]
                );

        $this->assertEquals(self::TOOTID_ANSWER, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function testFindEndOfChainIfThereIsAThreadWithTwoPostsAndSomeoneIsMentionedInTheSecondPostAndThatPostIsADirectMessage(): void {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post <== THIS ONE
         * - tootid-answer from userid-op with mention of userid-bob which is a DM
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andReturn(
                    [
                        "descendants" => [
                            [
                                "id"                     => self::TOOTID_ANSWER,
                                "in_reply_to_id"         => self::TOOTID_OP,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "direct",
                                "account"                => ["id" => self::USERID_OP],
                                "mentions"               => [["id" => self::USERID_ANSWER]],
                            ]
                        ]
                    ]
                );

        $this->assertEquals(self::TOOTID_OP, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function testFindEndOfChainIfThereIsAThreadWithMultiplePosts(): void {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post
         * - tootid-answer from userid-op
         *   - tootid-answer2 from userid-op <== THIS ONE
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andReturn(
                    [
                        "descendants" => [
                            [
                                "id"                     => self::TOOTID_ANSWER,
                                "created_at"             => "2022-11-25T23:27:49.031Z",
                                "in_reply_to_id"         => self::TOOTID_OP,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "unlisted",
                                "content"                => "<p>Thread 1</p>",
                                "account"                => ["id" => self::USERID_OP],
                            ],
                            [
                                "id"                     => self::TOOTID_ANSWER_2,
                                "created_at"             => "2022-11-26T00:28:07.686Z",
                                "in_reply_to_id"         => self::TOOTID_ANSWER,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "unlisted",
                                "content"                => "<p>Thread 2</p>",
                                "account"                => ["id" => self::USERID_OP],
                            ],
                        ],
                    ]
                );

        $this->assertEquals(self::TOOTID_ANSWER_2, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function testFindEndOfChainIfThereIsAThreadWithMultiplePostsAndSomeAnswers(): void {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post
         * - tootid-answer from userid-op
         *   - tootid-answer2 from userid-op <== THIS ONE
         *   - tootid-answer3 from userid-answer
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andReturn(
                    [
                        "descendants" => [
                            [
                                "id"                     => self::TOOTID_ANSWER,
                                "created_at"             => "2022-11-25T23:27:49.031Z",
                                "in_reply_to_id"         => self::TOOTID_OP,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "unlisted",
                                "content"                => "<p>2/3</p>",
                                "account"                => ["id" => self::USERID_OP],
                            ],
                            [
                                "id"                     => self::TOOTID_ANSWER_2,
                                "created_at"             => "2022-11-26T00:28:07.686Z",
                                "in_reply_to_id"         => self::TOOTID_ANSWER,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "unlisted",
                                "content"                => "<p>3/3</p>",
                                "account"                => ["id" => self::USERID_OP],
                            ],
                            [
                                "id"                     => self::TOOTID_ANSWER_3,
                                "created_at"             => "2022-11-25T22:28:33.000Z",
                                "in_reply_to_id"         => self::TOOTID_ANSWER,
                                "in_reply_to_account_id" => self::USERID_OP,
                                "visibility"             => "unlisted",
                                "content"                => "<p>Answer on 2/3 in thread</p>",
                                "account"                => ["id" => self::USERID_ANSWER],
                                "mentions"               => [["id" => self::USERID_OP]],
                            ],
                        ]
                    ]
                );

        $this->assertEquals(self::TOOTID_ANSWER_2, MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function testFindEndOfChainIfOriginalPostIsNotFound() {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post is deleted.
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andThrowExceptions([new ClientException(
                                          '{"error":"Record not found"}',
                                          new Request('GET', self::OP_CONTEXT_URL),
                                          new Response(404))]);

        Log::shouldReceive('error')->once();
        Log::shouldReceive('info')->twice();

        $this->assertNull(MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    public function testFindEndOfChainIfMastodonServerUnreachable() {
        $user = $this->setupUserWithMastodonAccount();

        /**
         * Original post is deleted.
         */

        Mastodon::shouldReceive('call')
                ->once()
                ->with("GET", self::OP_CONTEXT_URL, MastodonController::getRequestOptions())
                ->andThrowExceptions([new ConnectException("server not available",
                                                           new Request('GET', self::OP_CONTEXT_URL)
                                      )]);

        Log::shouldReceive('error')->once();
        Log::shouldReceive('info')->once();

        $this->assertNull(MastodonController::getEndOfChain($user, self::TOOTID_OP));
    }

    private function setupUserWithMastodonAccount(): User {
        $user = User::factory()->create();

        $mastodonServer = MastodonServer
            ::create([
                         'domain'        => 'https://example.com',
                         'client_id'     => '123abc',
                         'client_secret' => '123abc'
                     ]);
        $socialProfile  = $mastodonServer
            ->socialProfiles()
            ->create([
                         'user_id'         => $user->id,
                         'mastodon_id'     => (int) self::USERID_OP,
                         'mastodon_server' => $mastodonServer->id,
                         'mastodon_token'  => 'my_mastodon_token'
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
