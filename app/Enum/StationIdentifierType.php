<?php

declare(strict_types=1);

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StationIdentifierType',
    description: 'The type of the station identifier to look up. Not all types are available for every station. Subject to unannounced change.
    * motis – all transitous.org/motis supplied identifiers
    * wikidata_id – ID of wikidata.org
    * de_db_ril100 – Germany: Deutsche Bahn Richtlinie 100 identifier (e.g. RK for Karlsruhe Hbf)
    * de_db_ibnr – Germany: internal train station ID of Deutsche Bahn (e.g. 8000191 for Karlsruhe Hbf)
    ',
    type: 'integer',
    example: 0,
    enum: ['motis', 'wikidata_id', 'ifopt', 'de_db_ril100', 'de_db_ibnr'],
)]
enum StationIdentifierType: string
{
    case MOTIS = 'motis';
    case WIKIDATA_ID = 'wikidata_id';
    case IFOPT = 'ifopt';              // International: Identification of Fixed Objects in Public Transport
    case DE_DB_RIL100 = 'de_db_ril100'; // Germany: Deutsche Bahn Richtline 100 identifier
    case DE_DB_IBNR = 'de_db_ibnr';   // Germany: internal train station ID
}
