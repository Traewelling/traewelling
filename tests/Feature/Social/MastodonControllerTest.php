<?php

namespace Tests\Feature\Social;

use App\Http\Controllers\Backend\Social\MastodonController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MastodonControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider providerTestFormatDomain
     */
    public function testFormatDomain($case, $expected): void {
        $formatted = MastodonController::formatDomain($case);
        $this->assertEquals($expected, $formatted);

        $validated = Validator::make(['domain' => $formatted], ['domain' => ['active_url']]);

        $this->assertFalse($validated->fails());
    }

    public function providerTestFormatDomain(): array {
        return [
            ['https://uelfte.club', 'https://uelfte.club'],
            ['http://uelfte.club', 'https://uelfte.club'],
            ['uelfte.club', 'https://uelfte.club'],
            ['great_username@uelfte.club', 'https://uelfte.club'],
            ['@great_username@uelfte.club', 'https://uelfte.club'],
            ['https://mastodon.sergal.org', 'https://mastodon.sergal.org'] # see issue 1182
        ];
    }
}
