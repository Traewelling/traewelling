<?php

namespace Tests\Unit;

use App\Http\Controllers\Backend\Helper\PrivacyHelper;
use InvalidArgumentException;

class PrivacyHelperTest extends UnitTestCase
{
    public function test_i_pv4_masking(): void
    {
        $masked = PrivacyHelper::maskIpAddress('127.0.0.1');
        $this->assertEquals('127.0.0.0', $masked);
    }

    public function test_i_pv6_masking(): void
    {
        $masked = PrivacyHelper::maskIpAddress('fe80:0001:1234:4321::af0');
        $this->assertEquals('fe80:1:1234:4321::', $masked);

        $masked = PrivacyHelper::maskIpAddress('fe80:0001:1234:4321:1234:4321:cafe:affe');
        $this->assertEquals('fe80:1:1234:4321:1234::', $masked);
    }

    public function test_invalid_argument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PrivacyHelper::maskIpAddress('fe80.1234::');
    }
}
