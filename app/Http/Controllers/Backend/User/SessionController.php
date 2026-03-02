<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;

abstract class SessionController extends Controller
{
    public static function index(User $user): object
    {
        return $user->sessions->map(function ($session) {
            $ua = (string) $session->user_agent;
            $session->platform = self::detectPlatform($ua);
            $session->device_icon = self::detectDeviceIcon($ua);

            return $session;
        });
    }

    public static function deleteAllSessionsFor(User $user): void
    {
        $user->sessions()->delete();
    }

    public static function detectPlatform(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPod') => 'iOS',
            str_contains($userAgent, 'iPad') => 'iPadOS',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'CrOS') => 'Chrome OS',
            str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown',
        };
    }

    public static function detectDeviceIcon(string $userAgent): string
    {
        if (
            str_contains($userAgent, 'iPad')
            || (str_contains($userAgent, 'Android') && !str_contains($userAgent, 'Mobile'))
        ) {
            return 'tablet';
        }

        if (
            str_contains($userAgent, 'iPhone')
            || str_contains($userAgent, 'iPod')
            || str_contains($userAgent, 'BlackBerry')
            || str_contains($userAgent, 'Windows Phone')
            || (str_contains($userAgent, 'Android') && str_contains($userAgent, 'Mobile'))
        ) {
            return 'mobile-alt';
        }

        return 'desktop';
    }
}
