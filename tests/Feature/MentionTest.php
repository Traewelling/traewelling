<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\Support\MentionHelper;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class MentionTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testCreateMentions(): void {
        User::factory()->create(['username' => 'alice']);
        User::factory()->create(['username' => 'bob']);
        $body = 'I\'m on my way with @alice and @bob';

        $status = Status::factory()->create(['body' => $body]);

        $this->assertSame(2, $status->mentions->count());

        // test MentionHelper
        $helper   = new MentionHelper($status, $body);
        $mentions = $helper->findUsersInString();
        $this->assertCount(2, $mentions);
        $mentionDto     = $mentions[0];
        $jsonSerialized = $mentionDto->jsonSerialize();
        $this->assertIsArray($jsonSerialized);
        $this->assertArrayHasKey('user', $jsonSerialized);
        $this->assertArrayHasKey('position', $jsonSerialized);
        $this->assertArrayHasKey('length', $jsonSerialized);
    }

    public function testDeleteMentions(): void {
        User::factory()->create(['username' => 'alice']);
        User::factory()->create(['username' => 'bob']);
        $body = 'I\'m on my way with @alice and @bob';

        $status = Status::factory()->create(['body' => $body]);

        $status->update(['body' => 'I\'m on my way with @alice']);
        $status->refresh();
        $this->assertSame(1, $status->mentions->count());

        $status->update(['body' => '@alice']);
        $status->refresh();
        $this->assertSame(1, $status->mentions->count());

        $status->update(['body' => '']);
        $status->refresh();
        $this->assertSame(0, $status->mentions->count());

        $status->update(['body' => '@alice']);
        $status->refresh();
        $this->assertSame(1, $status->mentions->count());

        $status->update(['body' => null]);
        $status->refresh();
        $this->assertSame(0, $status->mentions->count());
    }

    public function testUpdateMentions(): void {
        User::factory()->create(['username' => 'alice']);
        User::factory()->create(['username' => 'bob']);
        User::factory()->create(['username' => 'charlie']);
        $body = 'I\'m on my way with @alice and @bob';

        $status = Status::factory()->create(['body' => $body]);

        $status->update(['body' => 'I\'m on my way with @alice and @bob and @charlie']);
        $status->refresh();
        $this->assertSame(3, $status->mentions->count());
    }

    public function testWithoutMentions(): void {
        $status = Status::factory()->create(['body' => 'hi there!']);
        $this->assertSame(0, $status->mentions->count());
    }

    public function testWithWrongMentions(): void {
        $status = Status::factory()->create(['body' => 'omw w/@ alice']);
        $this->assertSame(0, $status->mentions->count());
    }

    public function testHtmlBody(): void {
        User::factory()->create(['username' => 'alice']);
        User::factory()->create(['username' => 'bob']);

        $status = Status::factory()->create(['body' => 'I\'m on my way with @alice and @bob']);
        $this->assertSame(
            'I&#039;m on my way with <a href="' . route('profile', 'alice') . '">@alice</a> '
            . 'and <a href="' . route('profile', 'bob') . '">@bob</a>',
            MentionHelper::getBodyWithMentionLinks($status)
        );

        $status->update(['body' => 'I\'m on my way with @alice and @alice']);
        $status->refresh();
        $this->assertSame(
            'I&#039;m on my way with <a href="' . route('profile', 'alice') . '">@alice</a> '
            . 'and <a href="' . route('profile', 'alice') . '">@alice</a>',
            MentionHelper::getBodyWithMentionLinks($status)
        );
    }

    public function testMentionNotification(): void {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob   = User::factory()->create(['username' => 'bob']);

        $status = Status::factory()->create(['body' => 'I\'m on my way with @alice and @bob']);
        $status->refresh();

        $this->assertSame(1, $alice->notifications->count());
        $this->assertSame(1, $bob->notifications->count());
    }

    public function testNoMentionNotificationOnUpdate(): void {
        $alice = User::factory()->create(['username' => 'alice']);

        $status = Status::factory()->create(['body' => 'I\'m on my way with @alice']);
        $status->refresh();
        $this->assertSame(1, $alice->notifications->count());

        $status->update(['body' => 'I\'m on my way with only @alice']);
        $status->refresh();

        $this->assertSame(1, $alice->notifications->count());
    }
}
