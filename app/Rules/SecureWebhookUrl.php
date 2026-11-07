<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class SecureWebhookUrl implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if (is_string($value) && self::isAllowedUrl($value)) {
            return;
        }

        $fail(':attribute must be an https URL pointing to a public host.');
    }

    public static function isAllowedUrl(string $url): bool
    {
        return self::resolveAllowedTargetAddresses($url) !== null;
    }

    /**
     * Resolves the host of the given URL and returns its addresses, provided the
     * URL is an https URL and every resolved address is public.
     *
     * @return list<string>|null
     */
    public static function resolveAllowedTargetAddresses(string $url): ?array
    {
        if (!str_starts_with($url, 'https://')) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return null;
        }

        $host = trim($host, '[]');

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return self::isPublicAddress($host) ? [$host] : null;
        }

        $addresses = self::resolveHost($host);

        if ($addresses === []) {
            return null;
        }

        foreach ($addresses as $address) {
            if (!self::isPublicAddress($address)) {
                return null;
            }
        }

        return $addresses;
    }

    public static function isPublicAddress(string $address): bool
    {
        $packed = @inet_pton($address);

        if ($packed === false) {
            return false;
        }

        foreach (self::parsedBlockedRanges() as [$network, $prefixLength]) {
            if (self::matchesPrefix($packed, $network, $prefixLength)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private static function resolveHost(string $host): array
    {
        $addresses = [];

        foreach ([DNS_A, DNS_AAAA] as $type) {
            foreach (@dns_get_record($host, $type) ?: [] as $record) {
                if (isset($record['ip'])) {
                    $addresses[] = $record['ip'];
                }

                if (isset($record['ipv6'])) {
                    $addresses[] = $record['ipv6'];
                }
            }
        }

        if ($addresses === []) {
            $fallback = @gethostbynamel($host);

            if ($fallback !== false) {
                $addresses = $fallback;
            }
        }

        return array_values(array_unique($addresses));
    }

    /**
     * @return list<array{string, int}>
     */
    private static function parsedBlockedRanges(): array
    {
        static $ranges = null;

        if ($ranges === null) {
            $ranges = [];

            foreach (self::BLOCKED_RANGES as $range) {
                [$network, $prefixLength] = explode('/', $range);
                $ranges[] = [inet_pton($network), (int) $prefixLength];
            }
        }

        return $ranges;
    }

    private static function matchesPrefix(string $address, string $network, int $prefixLength): bool
    {
        $bytes = intdiv($prefixLength, 8);
        $bits = $prefixLength % 8;

        if (substr($address, 0, $bytes) !== substr($network, 0, $bytes)) {
            return false;
        }

        if ($bits === 0) {
            return true;
        }

        $mask = 0xFF << (8 - $bits) & 0xFF;

        return (ord($address[$bytes]) & $mask) === (ord($network[$bytes]) & $mask);
    }

    private const BLOCKED_RANGES = [
        // IPv4
        '0.0.0.0/8',
        '10.0.0.0/8',
        '100.64.0.0/10',
        '127.0.0.0/8',
        '169.254.0.0/16',
        '172.16.0.0/12',
        '192.0.0.0/24',
        '192.0.2.0/24',
        '192.88.99.0/24',
        '192.168.0.0/16',
        '198.18.0.0/15',
        '198.51.100.0/24',
        '203.0.113.0/24',
        '224.0.0.0/4',
        '240.0.0.0/4',
        // IPv6
        '::/128',
        '::1/128',
        '::ffff:0:0/96',
        '64:ff9b::/96',
        '100::/64',
        '2001::/32',
        '2001:2::/48',
        '2001:db8::/32',
        '2002::/16',
        'fc00::/7',
        'fe80::/10',
        'ff00::/8',
    ];
}
