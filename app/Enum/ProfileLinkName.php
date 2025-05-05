<?php

namespace App\Enum;

enum ProfileLinkName: string
{
    case INSTAGRAM = 'instagram';
    case TWITTER = 'twitter';
    case FACEBOOK = 'facebook';
    case LINKEDIN = 'linkedin';
    case MASTODON = 'mastodon';
    case TIKTOK = 'tiktok';
    case GITHUB = 'github';
    case WEBSITE = 'website';

    public function getName(): string
    {
        return match ($this) {
            self::INSTAGRAM => 'Instagram',
            self::TWITTER => 'Twitter',
            self::FACEBOOK => 'Facebook',
            self::LINKEDIN => 'LinkedIn',
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
            self::LINKEDIN => 'fa-brands fa-linkedin',
            self::MASTODON => 'fa-brands fa-mastodon',
            self::TIKTOK => 'fa-brands fa-tiktok',
            self::GITHUB => 'fa-brands fa-github',
            self::WEBSITE => 'fa-solid fa-globe',
        };
    }
}
