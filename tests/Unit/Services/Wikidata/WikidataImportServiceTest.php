<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Wikidata;

use App\Dto\Wikidata\WikidataEntity;
use App\Exceptions\Wikidata\FetchException;
use App\Models\Station;
use App\Services\Wikidata\WikidataImportService;
use App\Enum\StationIdentifierType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Unit\UnitTestCase;

class WikidataImportServiceTest extends UnitTestCase
{
    use RefreshDatabase;

    /**
     * Minimal entity response for Q688541 (Karlsruhe Hbf).
     * Contains: P31=Q55490 (Durchgangsbahnhof), P954 IBNR, P8671 RIL100,
     *           P12393 IFOPT, P625 coordinates, P1448 official name.
     */
    private function karlsruheHbfEntityData(): array
    {
        return [
            'entities' => [
                'Q688541' => [
                    'labels' => [
                        'de' => ['language' => 'de',  'value' => 'Karlsruhe Hauptbahnhof'],
                        'mul' => ['language' => 'mul', 'value' => 'Karlsruhe Hbf'],
                        'en' => ['language' => 'en',  'value' => 'Karlsruhe Central Station'],
                    ],
                    'descriptions' => [],
                    'aliases' => [],
                    'claims' => [
                        'P31' => [
                            self::wikidataItemClaim('P31', 'Q55490', 55490), // Durchgangsbahnhof
                            self::wikidataItemClaim('P31', 'Q18543139', 18543139), // Hauptbahnhof
                            self::wikidataItemClaim('P31', 'Q27996466', 27996466), // Bahnhof (betrieblich)
                        ],
                        'P954' => [self::stringClaim('P954', '8000191')],   // IBNR
                        'P8671' => [self::stringClaim('P8671', 'RK')],       // RIL100
                        'P12393' => [self::stringClaim('P12393', 'de:08212:90')], // IFOPT
                        'P625' => [self::coordinateClaim(48.993888888889, 8.4005555555556)],
                        'P1448' => [self::monolingualClaim('P1448', 'Karlsruhe Hbf', 'de')],
                    ],
                ],
            ],
        ];
    }

    /**
     * Minimal entity response for Q124398332 (Träwelling).
     * P31 values are Q1130645 and Q4420972, neither of which is in SUPPORTED_TYPES.
     */
    private function traewellingEntityData(): array
    {
        return [
            'entities' => [
                'Q124398332' => [
                    'labels' => [
                        'de' => ['language' => 'de', 'value' => 'Träwelling'],
                        'en' => ['language' => 'en', 'value' => 'Träwelling'],
                    ],
                    'descriptions' => [],
                    'aliases' => [],
                    'claims' => [
                        'P31' => [
                            self::wikidataItemClaim('P31', 'Q1130645', 1130645),
                            self::wikidataItemClaim('P31', 'Q4420972', 4420972),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_is_type_supported_returns_true_for_station(): void
    {
        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($this->karlsruheHbfEntityData()),
        ]);

        $entity = WikidataEntity::fetch('Q688541');

        $this->assertTrue(WikidataImportService::isTypeSupported($entity));
    }

    public function test_is_type_supported_returns_false_for_software(): void
    {
        Http::fake([
            '*/Special:EntityData/Q124398332.json' => Http::response($this->traewellingEntityData()),
        ]);

        $entity = WikidataEntity::fetch('Q124398332');

        $this->assertFalse(WikidataImportService::isTypeSupported($entity));
    }

    public function test_is_type_supported_returns_false_when_no_p31_claims(): void
    {
        Http::fake([
            '*/Special:EntityData/Q1.json' => Http::response([
                'entities' => [
                    'Q1' => [
                        'labels' => ['en' => ['language' => 'en', 'value' => 'Universe']],
                        'descriptions' => [],
                        'aliases' => [],
                        'claims' => [], // no P31
                    ],
                ],
            ]),
        ]);

        $entity = WikidataEntity::fetch('Q1');

        $this->assertFalse(WikidataImportService::isTypeSupported($entity));
    }

    public function test_get_coordinates_returns_coordinate_from_p625(): void
    {
        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($this->karlsruheHbfEntityData()),
        ]);

        $entity = WikidataEntity::fetch('Q688541');
        $coordinates = WikidataImportService::getCoordinates($entity);

        $this->assertNotNull($coordinates);
        $this->assertEqualsWithDelta(48.993888888889, $coordinates->latitude, 0.000001);
        $this->assertEqualsWithDelta(8.4005555555556, $coordinates->longitude, 0.000001);
    }

    public function test_get_coordinates_returns_null_when_p625_missing(): void
    {
        Http::fake([
            '*/Special:EntityData/Q124398332.json' => Http::response($this->traewellingEntityData()),
        ]);

        $entity = WikidataEntity::fetch('Q124398332');
        $coordinates = WikidataImportService::getCoordinates($entity);

        $this->assertNull($coordinates);
    }

    public function test_import_station_creates_station_with_correct_name_and_coordinates(): void
    {
        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($this->karlsruheHbfEntityData()),
        ]);

        $station = WikidataImportService::importStation('Q688541');

        $this->assertInstanceOf(Station::class, $station);
        $this->assertSame('Karlsruhe Hbf', $station->name); // P1448 official name
        $this->assertEqualsWithDelta(48.993888888889, $station->latitude, 0.000001);
        $this->assertEqualsWithDelta(8.4005555555556, $station->longitude, 0.000001);
        $this->assertSame('wikidata', $station->source);
    }

    public function test_import_station_stores_all_identifiers(): void
    {
        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($this->karlsruheHbfEntityData()),
        ]);

        $station = WikidataImportService::importStation('Q688541');

        $identifiers = $station->stationIdentifiers()->pluck('identifier', 'type');

        $this->assertSame('Q688541', $identifiers[StationIdentifierType::WIKIDATA_ID->value]);
        $this->assertSame('8000191', $identifiers[StationIdentifierType::DE_DB_IBNR->value]);
        $this->assertSame('RK', $identifiers[StationIdentifierType::DE_DB_RIL100->value]);
        $this->assertSame('de:08212:90', $identifiers[StationIdentifierType::IFOPT->value]);
    }

    public function test_import_station_falls_back_to_german_label_when_no_p1448(): void
    {
        $data = $this->karlsruheHbfEntityData();
        unset($data['entities']['Q688541']['claims']['P1448']);

        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($data),
        ]);

        $station = WikidataImportService::importStation('Q688541');

        $this->assertSame('Karlsruhe Hauptbahnhof', $station->name);
    }

    public function test_import_station_falls_back_to_mul_label_when_no_de_label(): void
    {
        $data = $this->karlsruheHbfEntityData();
        unset($data['entities']['Q688541']['claims']['P1448']);
        unset($data['entities']['Q688541']['labels']['de']);

        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($data),
        ]);

        $station = WikidataImportService::importStation('Q688541');

        $this->assertSame('Karlsruhe Hbf', $station->name);
    }

    public function test_import_station_falls_back_to_english_label(): void
    {
        $data = $this->karlsruheHbfEntityData();
        unset($data['entities']['Q688541']['claims']['P1448']);
        unset($data['entities']['Q688541']['labels']['de']);
        unset($data['entities']['Q688541']['labels']['mul']);

        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($data),
        ]);

        $station = WikidataImportService::importStation('Q688541');

        $this->assertSame('Karlsruhe Central Station', $station->name);
    }

    public function test_import_station_throws_for_unsupported_type(): void
    {
        Http::fake([
            '*/Special:EntityData/Q124398332.json' => Http::response($this->traewellingEntityData()),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Q124398332 is not a supported type');

        WikidataImportService::importStation('Q124398332');
    }

    public function test_import_station_throws_when_no_name_available(): void
    {
        $data = $this->karlsruheHbfEntityData();
        unset($data['entities']['Q688541']['claims']['P1448']);
        $data['entities']['Q688541']['labels'] = []; // no labels at all

        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($data),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No name found for entity Q688541');

        WikidataImportService::importStation('Q688541');
    }

    public function test_import_station_throws_when_no_coordinates(): void
    {
        $data = $this->karlsruheHbfEntityData();
        unset($data['entities']['Q688541']['claims']['P625']);

        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($data),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No coordinates found for entity Q688541');

        WikidataImportService::importStation('Q688541');
    }

    public function test_import_station_returns_existing_station_when_ibnr_already_in_database(): void
    {
        $existing = Station::factory()->create(['name' => 'Existing Karlsruhe Hbf']);
        $existing->stationIdentifiers()->create([
            'type' => StationIdentifierType::DE_DB_IBNR->value,
            'identifier' => '8000191',
        ]);

        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($this->karlsruheHbfEntityData()),
        ]);

        $station = WikidataImportService::importStation('Q688541');

        $this->assertSame($existing->id, $station->id);
        $this->assertSame('Existing Karlsruhe Hbf', $station->name); // name unchanged
        $this->assertDatabaseCount('train_stations', 1);
    }

    public function test_import_station_adds_wikidata_identifier_to_existing_station(): void
    {
        $existing = Station::factory()->create();
        $existing->stationIdentifiers()->create([
            'type' => StationIdentifierType::DE_DB_IBNR->value,
            'identifier' => '8000191',
        ]);

        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($this->karlsruheHbfEntityData()),
        ]);

        WikidataImportService::importStation('Q688541');

        $identifiers = $existing->stationIdentifiers()->pluck('identifier', 'type');

        $this->assertSame('Q688541', $identifiers[StationIdentifierType::WIKIDATA_ID->value]);
        $this->assertSame('RK', $identifiers[StationIdentifierType::DE_DB_RIL100->value]);
    }

    public function test_search_station_throws_when_station_has_no_ibnr(): void
    {
        $station = Station::factory()->create();
        // no IBNR identifier added

        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('No IBNR identifier found for station');

        WikidataImportService::searchStation($station);
    }

    private static function wikidataItemClaim(string $property, string $id, int $numericId): array
    {
        return [
            'mainsnak' => [
                'snaktype' => 'value',
                'property' => $property,
                'datavalue' => [
                    'value' => [
                        'entity-type' => 'item',
                        'numeric-id' => $numericId,
                        'id' => $id,
                    ],
                    'type' => 'wikibase-entityid',
                ],
                'datatype' => 'wikibase-item',
            ],
            'type' => 'statement',
            'rank' => 'normal',
        ];
    }

    private static function stringClaim(string $property, string $value): array
    {
        return [
            'mainsnak' => [
                'snaktype' => 'value',
                'property' => $property,
                'datavalue' => ['value' => $value, 'type' => 'string'],
                'datatype' => 'external-id',
            ],
            'type' => 'statement',
            'rank' => 'normal',
        ];
    }

    private static function coordinateClaim(float $latitude, float $longitude): array
    {
        return [
            'mainsnak' => [
                'snaktype' => 'value',
                'property' => 'P625',
                'datavalue' => [
                    'value' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'altitude' => null,
                        'precision' => 2.7777777777778E-4,
                        'globe' => 'http://www.wikidata.org/entity/Q2',
                    ],
                    'type' => 'globecoordinate',
                ],
                'datatype' => 'globe-coordinate',
            ],
            'type' => 'statement',
            'rank' => 'normal',
        ];
    }

    private static function monolingualClaim(string $property, string $text, string $language): array
    {
        return [
            'mainsnak' => [
                'snaktype' => 'value',
                'property' => $property,
                'datavalue' => [
                    'value' => ['text' => $text, 'language' => $language],
                    'type' => 'monolingualtext',
                ],
                'datatype' => 'monolingualtext',
            ],
            'type' => 'statement',
            'rank' => 'normal',
        ];
    }
}
