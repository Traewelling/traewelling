<?php

namespace Tests\Unit\Social;

use App\Http\Controllers\Backend\Social\MastodonController;
use PHPUnit\Framework\TestCase;

class MastodonControllerTest extends TestCase
{

    /**
     * @dataProvider providerTestFormatDomain
     */
    public function testFormatDomain($case, $expected) {
        self::assertEquals($expected, MastodonController::formatDomain($case));
    }

    public function providerTestFormatDomain(): array {
        return [
            ['https://uelfte.club', 'https://uelfte.club'],
            ['http://uelfte.club', 'https://uelfte.club'],
            ['uelfte.club', 'https://uelfte.club'],
            ['great_username@uelfte.club', 'https://uelfte.club'],
            ['@great_username@uelfte.club', 'https://uelfte.club'],
        ];
    }
}
