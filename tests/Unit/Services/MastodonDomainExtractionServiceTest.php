<?php

namespace Tests\Unit\Services;

use App\Services\MastodonDomainExtractionService;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class MastodonDomainExtractionServiceTest extends UnitTestCase
{
    #[DataProvider('providerTestFormatDomain')]
    public function test_format_domain($case, $expected): void
    {
        $formatted = (new MastodonDomainExtractionService())->formatDomain($case);
        $this->assertEquals($expected, $formatted);

        $validated = Validator::make(['domain' => $formatted], ['domain' => ['active_url']]);

        $this->assertFalse($validated->fails());
    }

    public static function providerTestFormatDomain(): array
    {
        return [
            ['   https://github.com    ', 'https://github.com'],
            ['    ', ''],
            ['', ''],
            ['https://github.com', 'https://github.com'],
            ['http://github.com', 'https://github.com'],
            ['github.com', 'https://github.com'],
            ['great_username@github.com', 'https://github.com'],
            ['@great_username@github.com', 'https://github.com'],
            ['https://traewelling.github.io', 'https://traewelling.github.io'],
            ['https://traewelling.github.io/', 'https://traewelling.github.io'],
            ['https://traewelling.github.io/@foobar', 'https://traewelling.github.io'],
            ['github.com/mastodon/mastodon', 'https://github.com'],
            ['@user@github.com/@mastodon/mastodon', 'https://github.com'], // who would do this?
        ];
    }
}
