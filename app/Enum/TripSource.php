<?php declare(strict_types=1);

namespace App\Enum;

enum TripSource: string
{
    /**
     * Trips created by data from DB-Rest (HAFAS Deutsche Bahn).
     * @see https://v5.db.transport.rest/
     */
    case HAFAS = 'hafas';

    /**
     * Trips created by the user - with manual data.
     */
    case USER = 'user';
}
