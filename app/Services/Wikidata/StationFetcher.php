<?php declare(strict_types=1);

namespace App\Services\Wikidata;

use Illuminate\Support\Facades\Http;

abstract class StationFetcher
{

    private const SUPPORTED_INSTACE_OF = ['Q27996466'];

    public static function test() {
        self::importStationFromWikidata('Q124610138');
    }

    public static function importStationFromWikidata(string $wikidataId): void {
        // Fetch data from Wikidata

        $json = Http::get('https://www.wikidata.org/wiki/Special:EntityData/' . $wikidataId . '.json')
                    ->json();

        $entity = $json['entities'][$wikidataId];
        // check if P31 is supported
        if (!isset($entity['claims']['P31'])) {
            echo "No P31\n";
            return;
        }

        if (!self::isSupportedEntity($entity)) {
            echo "Unsupported entity\n";
            return;
        }

        $labelDe = self::getLabel($entity, 'de');
        $labelEn = self::getLabel($entity, 'en');
        $ifopt   = self::getClaimValue($entity, 'P12393');
        $ibnr    = self::getClaimValue($entity, 'P954');
        $ril100  = self::getClaimValue($entity, 'P8671');

        // Save data to database
        echo "Save to database\n";
        dump($entity);

        echo "Label DE: $labelDe\n";
        echo "Label EN: $labelEn\n";
        echo "Ifopt: $ifopt\n";
        echo "IBNR: $ibnr\n";
        echo "RIL100: $ril100\n";
    }

    private static function isSupportedEntity(array $entity): bool {
        $entityInstancesOf = $entity['claims']['P31'];

        foreach ($entityInstancesOf as $instanceOf) {
            if (in_array($instanceOf['mainsnak']['datavalue']['value']['id'], self::SUPPORTED_INSTACE_OF)) {
                return true;
            }
        }
        return false;
    }

    private static function getLabel(array $entity, string $language): string {
        return $entity['labels'][$language]['value'];
    }

    private static function getClaimValue(array $entity, string $property): ?string {
        return $entity['claims'][$property][0]['mainsnak']['datavalue']['value'] ?? null;
    }

}
