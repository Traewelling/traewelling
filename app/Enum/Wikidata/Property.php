<?php declare(strict_types=1);

namespace App\Enum\Wikidata;

enum Property: string
{
    case INSTANCE_OF                = 'P31';
    case TRANSPORT_NETWORK          = 'P16';
    case COUNTRY                    = 'P17';
    case COORDINATES                = 'P625';
    case DEUTSCHE_BAHN_STATION_CODE = 'P8671';
    case IBNR                       = 'P954';
    case IFOPT                      = 'P12393';

}
