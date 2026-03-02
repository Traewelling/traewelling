<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Backend\User\SessionController;
use Tests\Unit\UnitTestCase;

class SessionControllerTest extends UnitTestCase
{
    // --- detectPlatform ---

    public function test_detects_android(): void
    {
        $ua = 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';
        $this->assertSame('Android', SessionController::detectPlatform($ua));
    }

    public function test_detects_iphone_as_ios(): void
    {
        $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15';
        $this->assertSame('iOS', SessionController::detectPlatform($ua));
    }

    public function test_detects_ipad_as_ipados(): void
    {
        $ua = 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15';
        $this->assertSame('iPadOS', SessionController::detectPlatform($ua));
    }

    public function test_detects_windows(): void
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $this->assertSame('Windows', SessionController::detectPlatform($ua));
    }

    public function test_detects_macos(): void
    {
        $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';
        $this->assertSame('macOS', SessionController::detectPlatform($ua));
    }

    public function test_detects_chromeos(): void
    {
        $ua = 'Mozilla/5.0 (X11; CrOS x86_64 14541.0.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $this->assertSame('Chrome OS', SessionController::detectPlatform($ua));
    }

    public function test_detects_linux(): void
    {
        $ua = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $this->assertSame('Linux', SessionController::detectPlatform($ua));
    }

    public function test_returns_unknown_for_empty_ua(): void
    {
        $this->assertSame('Unknown', SessionController::detectPlatform(''));
    }

    // --- detectDeviceIcon ---

    public function test_phone_returns_mobile_alt_for_iphone(): void
    {
        $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15';
        $this->assertSame('mobile-alt', SessionController::detectDeviceIcon($ua));
    }

    public function test_phone_returns_mobile_alt_for_android_mobile(): void
    {
        $ua = 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';
        $this->assertSame('mobile-alt', SessionController::detectDeviceIcon($ua));
    }

    public function test_tablet_returns_tablet_for_ipad(): void
    {
        $ua = 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15';
        $this->assertSame('tablet', SessionController::detectDeviceIcon($ua));
    }

    public function test_tablet_returns_tablet_for_android_without_mobile(): void
    {
        $ua = 'Mozilla/5.0 (Linux; Android 13; SM-X710) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $this->assertSame('tablet', SessionController::detectDeviceIcon($ua));
    }

    public function test_desktop_returns_desktop_for_windows(): void
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $this->assertSame('desktop', SessionController::detectDeviceIcon($ua));
    }

    public function test_desktop_returns_desktop_for_mac(): void
    {
        $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';
        $this->assertSame('desktop', SessionController::detectDeviceIcon($ua));
    }

    public function test_desktop_returns_desktop_for_empty_ua(): void
    {
        $this->assertSame('desktop', SessionController::detectDeviceIcon(''));
    }
}
