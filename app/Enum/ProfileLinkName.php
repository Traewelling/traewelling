<?php

namespace App\Enum;

enum ProfileLinkName: string
{
    case WEBSITE = 'website';
    case INSTAGRAM = 'instagram';
    case BLUESKY = 'bluesky';
    case FACEBOOK = 'facebook';
    case MASTODON = 'mastodon';
    case TIKTOK = 'tiktok';
    case GITHUB = 'github';

    public function getName(): string
    {
        return match ($this) {
            self::INSTAGRAM => 'Instagram',
            self::BLUESKY => 'Bluesky',
            self::FACEBOOK => 'Facebook',
            self::MASTODON => 'Mastodon',
            self::TIKTOK => 'TikTok',
            self::GITHUB => 'GitHub',
            self::WEBSITE => 'Website',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::INSTAGRAM => 'fa-brands fa-instagram',
            self::BLUESKY => 'fa-brands fa-bluesky',
            self::FACEBOOK => 'fa-brands fa-facebook',
            self::MASTODON => 'fa-brands fa-mastodon',
            self::TIKTOK => 'fa-brands fa-tiktok',
            self::GITHUB => 'fa-brands fa-github',
            self::WEBSITE => 'fa-solid fa-globe',
        };
    }
}
