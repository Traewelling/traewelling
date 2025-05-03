<?php declare(strict_types=1);

namespace App\Enum;

enum TripSource: string
{
    /**
     * Trips created by data from DB-Rest (HAFAS Deutsche Bahn).
     * @see https://v5.db.transport.rest/
     */
    case HAFAS = 'hafas';

    case BAHN_WEB_API = 'bahn-web-api';
    case TRANSITOUS   = 'transitous';

    /**
     * Trips created by the user - with manual data.
     */
    case USER = 'user';

    public function identifiableById(): bool {
        return match ($this) {
            self::HAFAS, self::BAHN_WEB_API => true,
            default                         => false,
        };
    }

    public function refreshable(): bool {
        return match ($this) {
            self::HAFAS, self::BAHN_WEB_API => true,
            default                         => false,
        };
    }
}
