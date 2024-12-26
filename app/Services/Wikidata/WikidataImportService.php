<?php declare(strict_types=1);

namespace App\Services\Wikidata;

use App\Dto\Coordinate;
use App\Dto\Wikidata\WikidataEntity;
use App\Exceptions\Wikidata\FetchException;
use App\Models\Station;
use App\Models\StationName;
use Illuminate\Support\Facades\Log;

class WikidataImportService
{

    // supported types global definieren
    private const SUPPORTED_TYPES = [
        'Q55490', // Durchgangsbahnhof
        'Q18543139', // Hauptbahnhof
        'Q27996466', // Bahnhof (betrieblich)
        'Q55488', // Bahnhof (Verkehrsanlage einer Bahn)
        'Q124817561', // Betriebsstelle
        'Q644371', // internationaler Flughafen
        'Q21836433', // Flughafen
        'Q953806', // Bushaltestelle
        'Q2175765', // Straßenbahnhaltestelle
        'Q44782', // Hafen
    ];

    public static function importStation(string $qId): Station {
        $wikidataEntity = WikidataEntity::fetch($qId);

        if (!self::isTypeSupported($wikidataEntity)) {
            throw new \InvalidArgumentException('Entity ' . $qId . ' is not a supported type');
        }

        $name = $wikidataEntity->getClaims('P1448')[0]['mainsnak']['datavalue']['value']['text'] //P1448 = official name
                ?? $wikidataEntity->getLabel('de') //german label
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
            $splittedIfopt = explode(':', $ifopt);
        }

        //if ibnr is already in use, we can't import the station
        if ($ibnr !== null && Station::where('ibnr', $ibnr)->exists()) {
            throw new \InvalidArgumentException('IBNR ' . $ibnr . ' already in use');
        }

        return Station::create(
            [
                'name'          => $name,
                'latitude'      => $coordinates->latitude,
                'longitude'     => $coordinates->longitude,
                'wikidata_id'   => $qId,
                'rilIdentifier' => $rl100,
                'ibnr'          => $ibnr,
                'ifopt_a'       => $splittedIfopt[0] ?? null,
                'ifopt_b'       => $splittedIfopt[1] ?? null,
                'ifopt_c'       => $splittedIfopt[2] ?? null,
                'ifopt_d'       => $splittedIfopt[3] ?? null,
                'ifopt_e'       => $splittedIfopt[4] ?? null,
            ]
        );
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

        //get names
        foreach ($object->getClaims('P2561') as $property) {
            $text     = $property['mainsnak']['datavalue']['value']['text'] ?? null;
            $language = $property['mainsnak']['datavalue']['value']['language'] ?? null;
            if ($language === null || $text === null) {
                continue;
            }
            StationName::updateOrCreate([
                                            'station_id' => $station->id,
                                            'language'   => $language,
                                        ], [
                                            'name' => $text
                                        ]);
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
