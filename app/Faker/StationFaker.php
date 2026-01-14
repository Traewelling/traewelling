<?php

namespace App\Faker;

use Faker\Provider\Base;

class StationFaker extends Base
{
    private array $stations = [];

    /**
     * Using ~100 real stations from Wikidata with this query:
     *
     * SELECT DISTINCT ?station ?stationLabel ?coord ?ibnr ?RL100 ?ifopt ?patronage
     * WHERE {
     * ?station wdt:P31/wdt:P279* wd:Q55488;  # Instanz eines Bahnhofs
     * wdt:P17 ?country.       # ?country = Staat
     * ?country wdt:P30 wd:Q46.         # Kontinent = Europa
     * ?station wdt:P1373 ?patronage.
     * ?station wdt:P625 ?coord.
     * OPTIONAL { ?station wdt:P954 ?ibnr. }  # IBNR
     * OPTIONAL { ?station wdt:P8671 ?RL100. }   # UIC
     * OPTIONAL { ?station wdt:P12393 ?ifopt. } # IFOPT
     * SERVICE wikibase:label { bd:serviceParam wikibase:language "de,en". }
     * }
     * ORDER BY DESC (?patronage)
     * LIMIT 200
     */
    private function initStations(): void
    {
        if (!empty($this->stations)) {
            return;
        }
        $json = file_get_contents(__DIR__ . '/stations.json');
        $data = json_decode($json, true);
        $this->stations = $data;
    }

    private function getStations(): array
    {
        $this->initStations();

        return $this->stations;
    }

    public function station(): array
    {
        return static::randomElement($this->getStations());
    }
}
