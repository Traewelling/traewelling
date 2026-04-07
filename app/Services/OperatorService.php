<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\DataProvider;
use App\Models\Operator;
use App\Models\OperatorIdentifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OperatorService
{
    private const string MAPPING_FILE_PATH = 'storage/operator-mapping.csv';

    private const string OPERATOR_FILE_PATH = 'storage/operator-operators.csv';

    /**
     * Parses the given agency ID and agency name, and updates or creates an Operator.
     */
    public function parseTransitousOperator(?string $motisAgencyId, ?string $motisAgencyName, DataProvider $source): ?Operator
    {
        $motisAgencyId = trim($motisAgencyId ?? '');
        $motisAgencyName = trim($motisAgencyName ?? '');
        if (empty($motisAgencyId) || empty($motisAgencyName)) {
            Log::debug('Agency ID or name is null', [
                'agencyId' => $motisAgencyId,
                'agencyName' => $motisAgencyName,
            ]);

            return null;
        }

        // First: check if there is an entry in the database with the given motis_id and motis_name.
        $databaseOperator = OperatorIdentifier::where([
            'identifier' => $motisAgencyId,
            'type' => 'motis',
            'source' => 'transitous',
            'name' => $motisAgencyName,
        ])->with('operator')->first()?->operator ?? null;

        // If the operator is already linked with a wikidata ID we don't need to evaluate any further
        if ($databaseOperator?->identifiers()->where('type', 'wikidata')->exists()) {
            Log::debug('Found existing operator in database with wikidata identifier', [
                'operatorId' => $databaseOperator->id,
                'wikidataId' => $databaseOperator->identifiers()->where('type', 'wikidata')->value('identifier'),
            ]);

            return $databaseOperator;
        }

        $operator = $this->findInMappings($motisAgencyId, $motisAgencyName, $source, $databaseOperator);

        if (!$operator) {
            // Fallback: If no mapping is found, create a new station & identifier.
            Log::debug('Fallback: Using agencyId for updateOrCreate', ['motis_id' => $motisAgencyId]);
            $operator = Operator::create(
                ['name' => $motisAgencyName]
            );
            $operator->identifiers()->updateOrCreate(
                [
                    'identifier' => $motisAgencyId,
                    'type' => $source->isMotis() ? 'motis' : 'hafas',
                    'source' => $source->value,
                    'name' => $motisAgencyName,
                ]
            );
        }

        return $operator;
    }

    /**
     * Loads the operator mappings from the CSV file.
     *
     * @return array Array of mappings (keys: motis_id, motis_name, wikidata_id).
     */
    private function loadOperatorMappings(): array
    {
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
    private static function loadOperatorOfficialNames(): array
    {
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
                $row = array_combine($header, $data);
                $wikidataId = $row['wikidata_id'] ?? null;
                $name = $row['name'] ?? null;
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
     * @param  array  $operatorMappings  The loaded operator mappings.
     * @param  string  $motisAgencyId  The agency ID.
     * @param  string  $motisAgencyName  The agency name.
     * @return array|null The found mapping or null if none is found.
     */
    private function findMapping(array $operatorMappings, string $motisAgencyId, string $motisAgencyName): ?array
    {
        Log::debug('Starting mapping search', [
            'agencyId' => $motisAgencyId,
            'agencyName' => $motisAgencyName,
        ]);

        foreach ($operatorMappings as $mapping) {
            if (
                isset($mapping['motis_id'])
                && (string) $mapping['motis_id'] === $motisAgencyId
                && isset($mapping['motis_name'])
                && strtolower(trim($mapping['motis_name'])) === strtolower(trim($motisAgencyName))
            ) {
                Log::debug('Mapping found', ['agencyId' => $motisAgencyId, 'agencyName' => $motisAgencyName]);

                return $mapping;
            }
        }

        Log::debug('No mapping found');

        return null;
    }

    public function refreshFiles(): void
    {
        $this->refreshFile('mapping', self::MAPPING_FILE_PATH);
        $this->refreshFile('operators', self::OPERATOR_FILE_PATH);
        $this->refreshOperators();
    }

    private function refreshFile(string $remoteFilename, string $localFilename): void
    {
        $response = Http::get("https://raw.githubusercontent.com/Traewelling/transitous-wikidata-operator-matching/refs/heads/main/$remoteFilename.csv");
        if ($response->successful()) {
            $csvContent = $response->body();
            $mappingFilePath = base_path($localFilename);
            file_put_contents($mappingFilePath, $csvContent);
            Log::debug('File updated', ['filePath' => $mappingFilePath]);
        } else {
            Log::error('Failed to fetch file.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }

    /**
     * After new operator.csv file is downloaded, this function is called to refresh the operators in the database.
     * Users can change the operator names in the CSV file, and this function will update the database accordingly.
     */
    private function refreshOperators(): void
    {
        $operators = $this->loadOperatorOfficialNames();
        foreach ($operators as $wikidataId => $name) {
            $existing = OperatorIdentifier::where('type', 'wikidata')
                ->where('identifier', $wikidataId)
                ->with('operator')
                ->first()
                ?->operator;

            if ($existing) {
                $existing->update(['name' => $name]);
            } else {
                $operator = Operator::create(['name' => $name]);
                $operator->identifiers()->create(['type' => 'wikidata', 'identifier' => $wikidataId]);
            }
        }
    }

    public function mergeOperators(Operator $oldOperator, Operator $newOperator): void
    {
        Log::debug('Merging operators', [
            'oldOperatorId' => $oldOperator->id,
            'newOperatorId' => $newOperator->id,
        ]);
        DB::transaction(function () use ($oldOperator, $newOperator) {
            // Update all trips to point to the new operator
            $oldOperator->trips()->update(['operator_id' => $newOperator->id]);

            // If the new operator already has a wikidata identifier, drop the old one before moving to avoid duplicates.
            if ($newOperator->identifiers()->where('type', 'wikidata')->exists()) {
                $oldOperator->identifiers()->where('type', 'wikidata')->delete();
            }

            $oldOperator->identifiers()->update(['operator_id' => $newOperator->id]);

            // Delete the old operator
            $oldOperator->delete();

            Log::debug('Operators merged successfully', [
                'oldOperatorId' => $oldOperator->id,
                'newOperatorId' => $newOperator->id,
                'wikidataId' => $newOperator->identifiers()->where('type', 'wikidata')->value('identifier'),
            ]);
        });
    }

    public function findInMappings(
        string $motisAgencyId,
        string $motisAgencyName,
        DataProvider $source,
        ?Operator $dbOperator = null,
    ): ?Operator {
        try {
            Log::debug('Starting operator parsing', [
                'agencyId' => $motisAgencyId,
                'agencyName' => $motisAgencyName,
            ]);

            // Load operator mappings from the CSV file.
            $operatorMappings = $this->loadOperatorMappings();
            Log::debug('Operator mappings loaded', ['mappingCount' => count($operatorMappings)]);

            // Find a matching mapping based on agency ID or agency name.
            $foundMapping = $this->findMapping($operatorMappings, $motisAgencyId, $motisAgencyName);
            $wikidataId = $foundMapping['wikidata_id'] ?? null;
            Log::debug('Mapping search result', ['mappingFound' => !is_null($foundMapping)]);

            // If a mapping with a valid wikidata_id is found:
            if ($foundMapping && !empty($foundMapping['wikidata_id']) && strtolower($foundMapping['wikidata_id']) !== 'null') {
                Log::debug('Found mapping with valid wikidata_id', ['wikidata_id' => $wikidataId]);

                // Load official operator names from the CSV file.
                $officialNames = $this->loadOperatorOfficialNames();
                $name = $officialNames[$wikidataId] ?? ($foundMapping['motis_name'] ?? $motisAgencyName);

                if ($dbOperator) {
                    Log::debug('Updating existing operator in database', [
                        'operatorId' => $dbOperator->id,
                        'wikidataId' => $wikidataId,
                        'name' => $name,
                    ]);

                    $existingByWikidata = OperatorIdentifier::where('type', 'wikidata')
                        ->where('identifier', $wikidataId)
                        ->with('operator')
                        ->first()
                        ?->operator;

                    if ($existingByWikidata) {
                        Log::debug('Wikidata ID already exists in database', [
                            'wikidataId' => $wikidataId,
                            'operatorId' => $existingByWikidata->id,
                            'motisAgencyId' => $motisAgencyId,
                            'motisAgencyName' => $motisAgencyName,
                        ]);

                        return $existingByWikidata;
                    }

                    // Link the existing operator to the wikidata ID and update the name.
                    $dbOperator->identifiers()->updateOrCreate(
                        ['type' => 'wikidata'],
                        ['identifier' => $wikidataId],
                    );

                    return $dbOperator->update(['name' => $name]) ? $dbOperator : null;
                }

                $operator = OperatorIdentifier::where('type', 'wikidata')
                    ->where('identifier', $wikidataId)
                    ->with('operator')
                    ->first()
                    ?->operator;

                if ($operator) {
                    $operator->update(['name' => $name]);
                } else {
                    $operator = Operator::create(['name' => $name]);
                    $operator->identifiers()->create(['type' => 'wikidata', 'identifier' => $wikidataId]);
                }
                $operator->identifiers()->updateOrCreate(
                    [
                        'identifier' => $motisAgencyId,
                        'type' => $source->isMotis() ? 'motis' : 'hafas',
                        'source' => $source->value,
                        'name' => $motisAgencyName,
                    ]
                );

                return $operator;
            }

            Log::debug('No match found in mappings', ['motis_id' => $motisAgencyId]);
        } catch (\Exception $exception) {
            Log::error('Error parsing operator', [
                'exception' => $exception,
            ]);
        }

        return $dbOperator ?? null;
    }
}
