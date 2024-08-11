<?php declare(strict_types=1);

namespace App\Services\Wikidata;

use App\Dto\Wikidata\WikidataEntity;
use App\Models\Station;

class WikidataImportService
{

    public static function importStation(string $qId): Station {
        $wikidataEntity = WikidataEntity::fetch($qId);

        $name = $wikidataEntity->getClaims('P1448')[0]['mainsnak']['datavalue']['value']['text'] //P1448 = official name
                ?? $wikidataEntity->getLabel('de') //german label
                   ?? $wikidataEntity->getLabel(); //english label or null if also not available

        if ($name === null) {
            throw new \InvalidArgumentException('No name found for entity ' . $qId);
        }

        $coordinates = $wikidataEntity->getClaims('P625')[0]['mainsnak']['datavalue']['value'] ?? null; //P625 = coordinate location
        if ($coordinates === null) {
            throw new \InvalidArgumentException('No coordinates found for entity ' . $qId);
        }

        $latitude  = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];
        $ibnr      = $wikidataEntity->getClaims('P954')[0]['mainsnak']['datavalue']['value'] ?? null;    //P954 = IBNR
        $rl100     = $wikidataEntity->getClaims('P8671')[0]['mainsnak']['datavalue']['value'] ?? null;   //P8671 = RL100
        $ifopt     = $wikidataEntity->getClaims('P12393')[0]['mainsnak']['datavalue']['value'] ?? null;  //P12393 = IFOPT
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
                'latitude'      => $latitude,
                'longitude'     => $longitude,
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

}
