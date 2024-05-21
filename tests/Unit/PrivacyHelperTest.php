<?php

namespace Tests\Unit;

use App;
use App\Http\Controllers\Backend\Helper\PrivacyHelper;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use InvalidArgumentException;
use Tests\FeatureTestCase;

class PrivacyHelperTest extends UnitTestCase
{

    public function testIPv4Masking(): void {
        $masked = PrivacyHelper::maskIpAddress('127.0.0.1');
        $this->assertEquals('127.0.0.0', $masked);
    }

    public function testIPv6Masking(): void {
        $masked = PrivacyHelper::maskIpAddress('fe80:0001:1234:4321::af0');
        $this->assertEquals('fe80:1:1234:4321::', $masked);

        $masked = PrivacyHelper::maskIpAddress('fe80:0001:1234:4321:1234:4321:cafe:affe');
        $this->assertEquals('fe80:1:1234:4321:1234::', $masked);
    }

    public function testInvalidArgument(): void {
        $this->expectException(InvalidArgumentException::class);
        PrivacyHelper::maskIpAddress('fe80.1234::');
    }
}
