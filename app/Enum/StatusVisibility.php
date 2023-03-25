<?php
declare(strict_types=1);

namespace App\Enum;

use App\Interfaces\IconEnumInterface;

enum StatusVisibility: int implements IconEnumInterface
{
    case PUBLIC = 0;
    case UNLISTED = 1;
    case FOLLOWERS = 2;
    case PRIVATE = 3;
    case AUTHENTICATED = 4;

    public function faIcon(): string {
        return match ($this) {
            self::PUBLIC        => 'fa-globe-americas',
            self::UNLISTED      => 'fa-lock-open',
            self::FOLLOWERS     => 'fa-user-friends',
            self::PRIVATE       => 'fa-lock',
            self::AUTHENTICATED => 'fa-user-check'
        };
    }

    public function title(): string {
        return match ($this) {
            self::PUBLIC        => __('status.visibility.0'),
            self::UNLISTED      => __('status.visibility.1'),
            self::FOLLOWERS     => __('status.visibility.2'),
            self::PRIVATE       => __('status.visibility.3'),
            self::AUTHENTICATED => __('status.visibility.4')
        };
    }

    public function description(): string {
        return match ($this) {
            self::PUBLIC        => __('status.visibility.0.detail'),
            self::UNLISTED      => __('status.visibility.1.detail'),
            self::FOLLOWERS     => __('status.visibility.2.detail'),
            self::PRIVATE       => __('status.visibility.3.detail'),
            self::AUTHENTICATED => __('status.visibility.4.detail')
        };
    }

    public static function keys(): array {
        return array_column(self::cases(), 'name');
    }

    public static function fromName(string $name): int {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status->value;
            }
        }
        throw new \ValueError("$name is not a valid backing value for enum " . self::class);
    }
}
