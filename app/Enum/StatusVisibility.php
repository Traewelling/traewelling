<?php
declare(strict_types=1);

namespace App\Enum;

enum StatusVisibility: int
{
    case PUBLIC = 0;
    case UNLISTED = 1;
    case FOLLOWERS = 2;
    case PRIVATE = 3;
    case AUTHENTICATED = 4;

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
