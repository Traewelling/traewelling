<?php declare(strict_types=1);

namespace App\Services;

use App\Models\HafasOperator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OperatorService
{
    private const string MAPPING_FILE_PATH  = 'storage/operator-mapping.csv';
    private const string OPERATOR_FILE_PATH = 'storage/operator-operators.csv';

    /**
     * Parses the given agency ID and agency name, and updates or creates an Operator.
     *
     * @param string|null $agencyId
     * @param string|null $agencyName
     *
     * @return HafasOperator|null
     */
    public function parseTransitousOperator(?string $agencyId, ?string $agencyName): ?HafasOperator {
        if (is_null($agencyId) || is_null($agencyName)) {
            Log::debug('Agency ID or name is null', [
                'agencyId'   => $agencyId,
                'agencyName' => $agencyName,
            ]);
            return null;
        }

        try {
            Log::debug('Starting operator parsing', [
                'agencyId'   => $agencyId,
                'agencyName' => $agencyName,
            ]);

            // Load operator mappings from the CSV file.
            $operatorMappings = $this->loadOperatorMappings();
            Log::debug('Operator mappings loaded', ['mappingCount' => count($operatorMappings)]);

            // Find a matching mapping based on agency ID or agency name.
            $foundMapping = $this->findMapping($operatorMappings, $agencyId, $agencyName);
            Log::debug('Mapping search result', ['mappingFound' => !is_null($foundMapping)]);

            // If a mapping with a valid wikidata_id is found:
            if ($foundMapping) {
                $wikidataId = $foundMapping['wikidata_id'] ?? null;

                // Check if a valid wikidata_id exists (not empty or "null").
                if (!$wikidataId || strtolower($wikidataId) === 'null') {
                    Log::debug('Found mapping without a valid wikidata_id', ['foundMapping' => $foundMapping]);
                    return null;
                }

                Log::debug('Found mapping with valid wikidata_id', ['wikidata_id' => $wikidataId]);

                // Load official operator names from the CSV file.
                $officialNames = $this->loadOperatorOfficialNames();
                $name          = $officialNames[$wikidataId] ?? ($foundMapping['motis_name'] ?? $agencyName);

                return HafasOperator::updateOrCreate(
                    ['wikidata_id' => $wikidataId],
                    ['name' => $name]
                );
            }

            // Fallback: If no mapping is found, use the agencyId.
            Log::debug('Fallback: Using agencyId for updateOrCreate', ['motis_id' => $agencyId]);
            return HafasOperator::updateOrCreate(
                ['motis_id' => $agencyId],
                ['name' => $agencyName]
            );
        } catch (\Exception $exception) {
            Log::error('Error parsing operator', [
                'exception' => $exception,
            ]);
            return null;
        }
    }

    /**
     * Loads the operator mappings from the CSV file.
     *
     * @return array Array of mappings (keys: motis_id, motis_name, wikidata_id).
     */
    private function loadOperatorMappings(): array {
        $mappingFilePath = base_path(self::MAPPING_FILE_PATH);
        Log::debug('Loading operator mapping CSV file', ['filePath' => $mappingFilePath]);

        if (!file_exists($mappingFilePath)) {
            Log::debug('Operator mapping file does not exist', ['filePath' => $mappingFilePath]);
            return [];
        }

        $operatorMappings = [];

        if (($handle = fopen($mappingFilePath, 'r')) !== false) {
            // Read the first line as header.
            $header = fgetcsv($handle);
            if ($header === false) {
                Log::debug('Error reading CSV header');
                fclose($handle);
                return [];
            }

            // Read each subsequent line as a mapping.
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) !== count($header)) {
                    continue; // Skip malformed rows.
                }
                $operatorMappings[] = array_combine($header, $data);
            }
            fclose($handle);
        }

        return $operatorMappings;
    }

    /**
     * Loads the official operator names from the CSV file.
     *
     * @return array Array that maps each wikidata_id to its official name.
     */
    private static function loadOperatorOfficialNames(): array {
        $operatorFilePath = base_path(self::OPERATOR_FILE_PATH);
        Log::debug('Loading official operator names from CSV', ['filePath' => $operatorFilePath]);

        if (!file_exists($operatorFilePath)) {
            Log::debug('Official operator names file does not exist', ['filePath' => $operatorFilePath]);
            return [];
        }

        $officialOperators = [];

        if (($handle = fopen($operatorFilePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            if ($header === false) {
                Log::debug('Error reading header from official names CSV');
                fclose($handle);
                return [];
            }

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) !== count($header)) {
                    continue; // Skip malformed rows.
                }
                $row        = array_combine($header, $data);
                $wikidataId = $row['wikidata_id'] ?? null;
                $name       = $row['name'] ?? null;
                if ($wikidataId && $name) {
                    $officialOperators[$wikidataId] = $name;
                }
            }
            fclose($handle);
        }

        return $officialOperators;
    }

    /**
     * Searches for a mapping using the agency ID and agency name.
     *
     * @param array  $operatorMappings The loaded operator mappings.
     * @param string $agencyId         The agency ID.
     * @param string $agencyName       The agency name.
     *
     * @return array|null The found mapping or null if none is found.
     */
    private function findMapping(array $operatorMappings, string $agencyId, string $agencyName): ?array {
        Log::debug('Starting mapping search', [
            'agencyId'   => $agencyId,
            'agencyName' => $agencyName,
        ]);

        foreach ($operatorMappings as $mapping) {
            if (
                isset($mapping['motis_id'])
                && (string) $mapping['motis_id'] === $agencyId
                && isset($mapping['motis_name'])
                && strtolower(trim($mapping['motis_name'])) === strtolower(trim($agencyName))
            ) {
                Log::debug('Mapping found', ['agencyId' => $agencyId, 'agencyName' => $agencyName]);
                return $mapping;
            }
        }

        Log::debug('No mapping found');
        return null;
    }

    public function refreshFiles(): void {
        $this->refreshFile('mapping', self::MAPPING_FILE_PATH);
        $this->refreshFile('operators', self::OPERATOR_FILE_PATH);
        $this->refreshOperators();
    }

    private function refreshFile(string $remoteFilename, string $localFilename): void {
        $response = Http::get("https://raw.githubusercontent.com/Traewelling/transitous-wikidata-operator-matching/refs/heads/main/$remoteFilename.csv");
        if ($response->successful()) {
            $csvContent      = $response->body();
            $mappingFilePath = base_path($localFilename);
            file_put_contents($mappingFilePath, $csvContent);
            Log::debug("File updated", ['filePath' => $mappingFilePath]);
        } else {
            Log::error('Failed to fetch file.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }
    }

    /**
     * After new operator.csv file is downloaded, this function is called to refresh the operators in the database.
     * Users can change the operator names in the CSV file, and this function will update the database accordingly.
     */
    private function refreshOperators(): void {
        $operators = $this->loadOperatorOfficialNames();
        foreach ($operators as $wikidataId => $name) {
            HafasOperator::updateOrCreate(
                ['wikidata_id' => $wikidataId],
                ['name' => $name]
            );
        }
    }

    public function mergeOperators(HafasOperator $oldOperator, HafasOperator $newOperator): void {
        DB::transaction(function() use ($oldOperator, $newOperator) {
            // Update all trips to point to the new operator
            $oldOperator->trips()->update(['operator_id' => $newOperator->id]);

            // Delete the old operator
            $oldOperator->delete();
        });
    }
}
