<?php

namespace App\Helpers;

class Formatter
{
    public static function simplifyStationName(string $stationName): string {
        // 1. Set to uppercase
        $stationName = strtoupper($stationName);

        // 2. Remove City names from public transport stations
        // "Hauptfriedhof, south entrance, Karlsruhe" -> "Hauptfriedhof, south entrance"
        $exploded = explode(',', $stationName);
        if (count($exploded) > 1) {
            array_pop($exploded);
            $stationName = implode(',', $exploded);
        }

        // 3. Remove special characters
        $stationName = preg_replace('/[^A-Za-z\s]/', '', $stationName);

        // 4. Remove words, characters and prefixes
        $removals = [
            // prefixes
            'H ',
            // words
            'FLIXTRAIN',
            'TIEF',
            'OBEN',
            'GLEIS',
            'PLATFORM',
            'GL',
            'BAHNHOF',
            'GARE',
            'STATION',
        ];
        foreach ($removals as $prefix) {
            $stationName = str_replace($prefix, '', $stationName);
        }

        // 5. Replace common words
        $replacements = [
            'CENTRALE'   => 'HBF',
            'CENTRAL'    => 'HBF',
            'HAUPT'      => 'HBF', // Hauptbahnhof
            'HLAVN NDRA' => 'HBF', // Hlavní nádraží without special characters
            'HLN'        => 'HBF', // Hlavní nádraží abbreviation
        ];
        foreach ($replacements as $search => $replace) {
            $stationName = str_replace($search, $replace, $stationName);
        }

        // 6. remove spaces
        return str_replace(' ', '', $stationName);
    }
}
