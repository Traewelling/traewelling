<?php

declare(strict_types=1);

namespace App\Enum;

enum StationIdentifierType: string
{
    case MOTIS = 'motis';
    case WIKIDATA_ID = 'wikidata_id';
    case IFOPT = 'ifopt';              // International: Identification of Fixed Objects in Public Transport
    case DE_DB_RIL100 = 'de_db_ril100'; // Germany: Deutsche Bahn Richtline 100 identifier
    case DE_DB_IBNR = 'de_db_ibnr';   // Germany: internal train station ID
}
