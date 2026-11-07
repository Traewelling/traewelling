<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\SecureWebhookUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class SecureWebhookUrlTest extends UnitTestCase
{
    public static function allowedUrlProvider(): array
    {
        return [
            'https to resolvable public host' => ['https://example.com/webhook'],
            'https to public host with port' => ['https://example.com:8443/webhook'],
            'https to public ipv4 literal' => ['https://8.8.8.8/webhook'],
            'https to public ipv6 literal' => ['https://[2a03:4000:3e:8ba:0:1ce:1ce:babe]/webhook'],
        ];
    }

    #[DataProvider('allowedUrlProvider')]
    public function test_accepts_https_urls_pointing_to_public_addresses(string $url): void
    {
        $this->assertTrue(SecureWebhookUrl::isAllowedUrl($url));
    }

    public static function rejectedUrlProvider(): array
    {
        return [
            'not a url' => ['example.com/webhook'],
            'other scheme' => ['ftp://example.com/webhook'],
            'plain http' => ['http://example.com/webhook'],
            'http ipv6 loopback with port' => ['http://127.0.0.1:18080/internal-admin'],
            'http localhost' => ['http://localhost:6379/'],
            'http ipv6 loopback' => ['http://[::1]/internal-admin'],
            'https ipv4 loopback' => ['https://127.0.0.1:18080/internal-admin'],
            'https localhost' => ['https://localhost/internal-admin'],
            'https private ipv4' => ['https://10.0.0.5/admin'],
            'https private ipv4 second range' => ['https://192.168.1.10/admin'],
            'https ipv4 cgnat range' => ['https://100.64.0.1/admin'],
            'https ipv4 link-local' => ['https://169.254.169.254/latest/meta-data/'],
            'https ipv4 multicast' => ['https://224.0.0.1/'],
            'https ipv6 loopback' => ['https://[::1]/admin'],
            'https ipv6 unique local' => ['https://[fd00::1]/admin'],
            'https ipv6 link-local' => ['https://[fe80::1]/admin'],
            'https ipv6 documentation' => ['https://[2001:db8::1]/admin'],
            'https ipv4-mapped loopback' => ['https://[::ffff:127.0.0.1]/admin'],
            'unresolvable host' => ['https://nonexistent-host.invalid/webhook'],
            'missing host' => ['https:///webhook'],
        ];
    }

    #[DataProvider('rejectedUrlProvider')]
    public function test_rejects_urls_pointing_to_non_public_or_unresolvable_addresses(string $url): void
    {
        $this->assertFalse(SecureWebhookUrl::isAllowedUrl($url));
    }

    public function test_validation_rule_fails_for_disallowed_url(): void
    {
        $rule = new SecureWebhookUrl();
        $failures = [];

        $fail = function (string $message) use (&$failures): void {
            $failures[] = $message;
        };

        $rule('authorizedWebhookUrl', 'https://10.0.0.5/admin', $fail);

        $this->assertCount(1, $failures);
        $this->assertStringContainsString('https URL pointing to a public host', $failures[0]);
    }

    public function test_validation_rule_passes_for_allowed_url(): void
    {
        $rule = new SecureWebhookUrl();
        $failures = [];

        $fail = function (string $message) use (&$failures): void {
            $failures[] = $message;
        };

        $rule('authorizedWebhookUrl', 'https://example.com/webhook', $fail);

        $this->assertSame([], $failures);
    }
}
