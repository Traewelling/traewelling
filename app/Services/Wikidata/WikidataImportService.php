<?php declare(strict_types=1);

namespace App\Services\Wikidata;

use App\Dto\Coordinate;
use App\Dto\Wikidata\WikidataEntity;
use App\Exceptions\Wikidata\FetchException;
use App\Models\Station;
use App\StationIdentifierType;
use Illuminate\Support\Facades\Log;

class WikidataImportService
{

    // supported types global definieren - todo: support wikidata hierarchie so we don't need to define all types separately
    private const array SUPPORTED_TYPES = [
        'Q55490', // Durchgangsbahnhof
        'Q18543139', // Hauptbahnhof
        'Q27996466', // Bahnhof (betrieblich)
        'Q27996460', // Haltepunkt
        'Q55488', // Bahnhof (Verkehrsanlage einer Bahn)
        'Q124817561', // Betriebsstelle
        'Q644371', // internationaler Flughafen
        'Q21836433', // Flughafen
        'Q1248784', // auch Flughafen
        'Q94993988', // Verkehrslandeplatz
        'Q1335652', // airport railway station
        'Q63979268', // people mover station
        'Q953806', // Bushaltestelle
        'Q2175765', // Straßenbahnhaltestelle
        'Q44782', // Hafen
        'Q15310171', // Seehafen
        'Q2296568', // river station
        'Q928830', // U-Bahnhof
        'Q22808403', // unterirdische Haltestelle
        'Q55485', // dead-end railway station
        'Q55491', // underground railway station
        'Q7886778', // union station
        'Q27996461', // Anschlussstelle
        'Q55493', // Güterbahnhof
        'Q519608', // Rangierbahnhof
        'Q65227640', // Betriebsbahnhof
        'Q336764', // Abzweigstelle
        'Q27996463', // Überleitstelle
        'Q44696264', //Seilbahnstation
        'Q1478783', // Fährhafen
        'Q4303352', // passenger ship terminal
        'Q55678', // railway stop, Haltepunkt, Haltestelle
        'Q494829', //bus station
    ];

    public static function importStation(string $qId): Station {
        $wikidataEntity = WikidataEntity::fetch($qId);

        if (!self::isTypeSupported($wikidataEntity)) {
            throw new \InvalidArgumentException('Entity ' . $qId . ' is not a supported type');
        }

        $name = $wikidataEntity->getClaims('P1448')[0]['mainsnak']['datavalue']['value']['text'] //P1448 = official name
                ?? $wikidataEntity->getLabel('de') //german label
                   ?? $wikidataEntity->getLabel('mul') //multilingual label
                      ?? $wikidataEntity->getLabel(); //english label or null if also not available

        if ($name === null) {
            throw new \InvalidArgumentException('No name found for entity ' . $qId);
        }

        $coordinates = self::getCoordinates($wikidataEntity);
        if ($coordinates === null) {
            throw new \InvalidArgumentException('No coordinates found for entity ' . $qId);
        }

        $ibnr  = $wikidataEntity->getClaims('P954')[0]['mainsnak']['datavalue']['value'] ?? null;    //P954 = IBNR
        $rl100 = $wikidataEntity->getClaims('P8671')[0]['mainsnak']['datavalue']['value'] ?? null;   //P8671 = RL100
        $ifopt = $wikidataEntity->getClaims('P12393')[0]['mainsnak']['datavalue']['value'] ?? null;  //P12393 = IFOPT
        if ($ifopt !== null) {
            $splitIfopt = explode(':', $ifopt);
        }

        //if ibnr is already in use, we can't import the station, but we can add the wikidata information to the existing station
        if ($ibnr !== null && Station::where('ibnr', $ibnr)->exists()) {
            $station              = Station::where('ibnr', $ibnr)->first();
            $station->wikidata_id = $qId;

            if ($station->ifopt_a === null && isset($splitIfopt)) {
                $station->ifopt_a = $splitIfopt[0] ?? null;
                $station->ifopt_b = $splitIfopt[1] ?? null;
                $station->ifopt_c = $splitIfopt[2] ?? null;
                $station->ifopt_d = $splitIfopt[3] ?? null;
                $station->ifopt_e = $splitIfopt[4] ?? null;
            }

            if ($station->rilIdentifier === null && $rl100 !== null) {
                $station->rilIdentifier = $rl100;
            }

            $station->save();

            return $station;
        }

        $station = Station::create(
            [
                'name'      => $name,
                'latitude'  => $coordinates->latitude,
                'longitude' => $coordinates->longitude,
                'ifopt_a'   => $splitIfopt[0] ?? null, // @deprecated: save in station_identifiers later
                'ifopt_b'   => $splitIfopt[1] ?? null, // @deprecated: save in station_identifiers later
                'ifopt_c'   => $splitIfopt[2] ?? null, // @deprecated: save in station_identifiers later
                'ifopt_d'   => $splitIfopt[3] ?? null, // @deprecated: save in station_identifiers later
                'ifopt_e'   => $splitIfopt[4] ?? null, // @deprecated: save in station_identifiers later
                'source'    => 'wikidata',
            ]
        );

        $station->stationIdentifiers()->create([
                                                   'type'       => StationIdentifierType::WIKIDATA_ID,
                                                   'identifier' => $qId,
                                               ]);

        if ($rl100) {
            $station->stationIdentifiers()->create([
                                                       'type'       => StationIdentifierType::DE_DB_RIL100,
                                                       'identifier' => $rl100,
                                                   ]);
        }

        if ($ibnr) {
            $station->stationIdentifiers()->create([
                                                       'type'       => StationIdentifierType::DE_DB_IBNR,
                                                       'identifier' => (string) $ibnr,
                                                   ]);
        }

        return $station;
    }

    /**
     * @throws FetchException
     */
    public static function searchStation(Station $station): void {
        // P054 = IBNR
        $sparqlQuery = <<<SPARQL
            SELECT ?item WHERE { ?item wdt:P954 "{$station->ibnr}". }
        SPARQL;

        $objects = (new WikidataQueryService())->setQuery($sparqlQuery)->execute()->getObjects();
        if (count($objects) > 1) {
            Log::debug('More than one object found for station ' . $station->ibnr . ' (' . $station->id . ') - skipping');
            throw new FetchException('There are multiple Wikidata entitied with IBNR ' . $station->ibnr);
        }

        if (empty($objects)) {
            Log::debug('No object found for station ' . $station->ibnr . ' (' . $station->id . ') - skipping');
            throw new FetchException('No Wikidata entity found for IBNR ' . $station->ibnr);
        }

        $object = $objects[0];
        $station->update(['wikidata_id' => $object->qId]);
        activity()->performedOn($station)->log('Linked wikidata entity ' . $object->qId);
        Log::debug('Fetched object ' . $object->qId . ' for station ' . $station->name . ' (Trwl-ID: ' . $station->id . ')');

        $ifopt = $object->getClaims('P12393')[0]['mainsnak']['datavalue']['value'] ?? null;
        if ($station->ifopt_a === null && $ifopt !== null) {
            $splitIfopt = explode(':', $ifopt);
            $station->update([
                                 'ifopt_a' => $splitIfopt[0] ?? null,
                                 'ifopt_b' => $splitIfopt[1] ?? null,
                                 'ifopt_c' => $splitIfopt[2] ?? null,
                             ]);
        }

        $rl100 = $object->getClaims('P8671')[0]['mainsnak']['datavalue']['value'] ?? null;
        if ($station->rilIdentifier === null && $rl100 !== null) {
            $station->update(['rilIdentifier' => $rl100]);
        }
    }

    public static function isTypeSupported(WikidataEntity $entity): bool {
        $instancesOf = $entity->getClaims('P31');
        foreach ($instancesOf as $instanceOf) {
            $instanceOfId = $instanceOf['mainsnak']['datavalue']['value']['id'];
            if (in_array($instanceOfId, self::SUPPORTED_TYPES)) {
                return true;
            }
        }
        return false;
    }

    public static function getCoordinates(WikidataEntity $entity): ?Coordinate {
        $coordinates = $entity->getClaims('P625')[0]['mainsnak']['datavalue']['value'] ?? null; //P625 = coordinate location
        if ($coordinates === null) {
            return null;
        }
        return new Coordinate($coordinates['latitude'], $coordinates['longitude']);
    }

}
