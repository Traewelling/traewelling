<?php

namespace App\Helpers;

class Formatter
{
    public static function cityStationName(string $stationName, ?string $city = null): string
    {
        if ($city) {
            $tempCity = preg_quote(strtoupper($city));
            $tempStationName = strtoupper($stationName);

            // if the station does not contain the city name as single word in regex, add it
            if (!preg_match('/\b' . $tempCity . '\b/', $tempStationName)) {
                $stationName = $stationName . ', ' . $city;
            }
        }

        return $stationName;
    }

    public static function simplifyStationName(string $stationName, ?string $city = null): string
    {
        // 1. Set to uppercase
        $stationName = strtoupper($stationName);

        // 3. Remove special characters
        $stationName = preg_replace('/[^A-Za-z\s]/', '', $stationName);

        if ($city) {
            $city = preg_replace('/[^A-Za-z\s]/', '', $city);
            // 2. Remove City names from station names
            // "Karlsruhe Hbf" -> "HBF"
            $city = '/\b' . preg_quote(strtoupper($city)) . '\b/';
            $stationName = preg_replace($city, '', $stationName);
        }

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
            'CENTRALE' => 'HBF',
            'CENTRAL' => 'HBF',
            'HAUPT' => 'HBF', // Hauptbahnhof
            'HLAVN NDRA' => 'HBF', // Hlavní nádraží without special characters
            'HLN' => 'HBF', // Hlavní nádraží abbreviation
        ];
        foreach ($replacements as $search => $replace) {
            $stationName = str_replace($search, $replace, $stationName);
        }

        // 6. remove spaces
        return str_replace(' ', '', $stationName);
    }

    public static function getCityFromAreas(array $areas): ?string
    {
        foreach ($areas as $area) {
            $default = $area['default'] ?? false;
            if ($default) {
                return $area['name'];
            }
        }

        return null;
    }
}
