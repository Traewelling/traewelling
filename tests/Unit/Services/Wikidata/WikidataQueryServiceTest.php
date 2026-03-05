<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Wikidata;

use App\Dto\Wikidata\WikidataEntity;
use App\Services\Wikidata\WikidataQueryService;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use Tests\Unit\UnitTestCase;

class WikidataQueryServiceTest extends UnitTestCase
{

    public function test_set_query_returns_self(): void
    {
        $service = new WikidataQueryService();
        $result = $service->setQuery('SELECT ?item WHERE { ?item wdt:P31 wd:Q55490. }');

        $this->assertSame($service, $result);
    }

    public function test_set_query_stores_query(): void
    {
        $service = new WikidataQueryService();
        $query = 'SELECT ?item WHERE { ?item wdt:P954 "8000191". }';
        $service->setQuery($query);

        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('sparqlQuery');

        $this->assertSame($query, $property->getValue($service));
    }

    public function test_get_objects_caches_parsed_objects(): void
    {
        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($this->karlsruheHbfEntityData()),
        ]);

        $service = new WikidataQueryService();
        $this->injectResults($service, new \ArrayObject([
            $this->sparqlRow('https://www.wikidata.org/entity/Q688541'),
        ]));

        // Call twice. Http should only be hit once (caching via $objects)
        $first = $service->getObjects();
        $second = $service->getObjects();

        $this->assertSame($first, $second);
        Http::assertSentCount(1);
    }

    public function test_get_objects_parses_qid_from_uri_and_fetches_entity(): void
    {
        Http::fake([
            '*/Special:EntityData/Q688541.json' => Http::response($this->karlsruheHbfEntityData()),
        ]);

        $service = new WikidataQueryService();
        $this->injectResults($service, new \ArrayObject([
            $this->sparqlRow('https://www.wikidata.org/entity/Q688541'),
        ]));

        $objects = $service->getObjects();

        $this->assertCount(1, $objects);
        $this->assertInstanceOf(WikidataEntity::class, $objects[0]);
        $this->assertSame('Q688541', $objects[0]->qId);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Inject pre-built SPARQL results into the service's private $results field,
     * bypassing the EasyRdf HTTP call in execute().
     */
    private function injectResults(WikidataQueryService $service, \Traversable $results): void
    {
        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('results');
        $property->setValue($service, $results);
    }

    /**
     * Build a minimal SPARQL result row that looks like EasyRdf's output:
     * an object with an "item" property whose getUri() returns the given URI.
     */
    private function sparqlRow(string $uri): object
    {
        $uriObj = new class($uri)
        {
            public function __construct(private string $uri) {}

            public function getUri(): string
            {
                return $this->uri;
            }
        };

        return (object) ['item' => $uriObj];
    }

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
                        'P31' => [self::wikidataItemClaim('P31', 'Q55490', 55490)],
                        'P954' => [self::stringClaim('P954', '8000191')],
                        'P8671' => [self::stringClaim('P8671', 'RK')],
                        'P625' => [self::coordinateClaim(48.993888888889, 8.4005555555556)],
                    ],
                ],
            ],
        ];
    }

    private static function wikidataItemClaim(string $property, string $id, int $numericId): array
    {
        return [
            'mainsnak' => [
                'snaktype' => 'value',
                'property' => $property,
                'datavalue' => [
                    'value' => ['entity-type' => 'item', 'numeric-id' => $numericId, 'id' => $id],
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
}
