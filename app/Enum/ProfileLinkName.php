<?php

namespace App\Enum;

enum ProfileLinkName: string
{
    case WEBSITE = 'website';
    case INSTAGRAM = 'instagram';
    case TWITTER = 'twitter';
    case FACEBOOK = 'facebook';
    case MASTODON = 'mastodon';
    case TIKTOK = 'tiktok';
    case GITHUB = 'github';

    public function getName(): string
    {
        return match ($this) {
            self::INSTAGRAM => 'Instagram',
            self::TWITTER => 'Twitter',
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
            self::TWITTER => 'fa-brands fa-twitter',
            self::FACEBOOK => 'fa-brands fa-facebook',
            self::MASTODON => 'fa-brands fa-mastodon',
            self::TIKTOK => 'fa-brands fa-tiktok',
            self::GITHUB => 'fa-brands fa-github',
            self::WEBSITE => 'fa-solid fa-globe',
        };
    }
}
