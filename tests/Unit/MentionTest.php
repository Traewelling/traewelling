<?php

namespace Tests\Unit;

use App\Http\Controllers\Backend\Support\MentionHelper;
use App\Models\Status;
use PHPUnit\Framework\TestCase;

class MentionTest extends TestCase
{

    /**
     * @dataProvider provideStringMatchesMentionDto
     */
    public function testStringMatchesMentionDto($string, $result): void
    {
        $this->assertSame($result, MentionHelper::findMentionsInString($string));
    }

    public function provideStringMatchesMentionDto(): array
    {
        return [
            ['I\'m on my way with @alice and @bob', [['@alice', 19], ['@bob', 30]]],
            ['@alice and @bob are waiting for me', [['@alice', 0], ['@bob', 11]]],
            ['@alice, atcha think?', [['@alice', 0]]],
            ['omw to #32c3 w/@alice', [['@alice', 15]]],
            ['omw w/@ alice', []],
            ['hi there!', []]
        ];
    }
}
