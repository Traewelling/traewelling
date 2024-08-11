<?php declare(strict_types=1);

namespace App\Services\Wikidata;

use App\Dto\Wikidata\WikidataEntity;
use EasyRdf\Sparql\Client;

class WikidataQueryService
{

    private string $sparqlEndpoint;
    private string $sparqlQuery;
    private        $results; //raw sparql results
    private array  $objects; //parsed objects

    public function __construct(string $sparqlEndpoint = 'https://query.wikidata.org/sparql') {
        $this->sparqlEndpoint = $sparqlEndpoint;
    }

    public function setQuery(string $sparqlQuery): self {
        $this->sparqlQuery = $sparqlQuery;
        return $this;
    }

    public function execute(): self {
        $this->results = (new Client($this->sparqlEndpoint))->query($this->sparqlQuery);
        return $this;
    }

    public function getObjects(): array {
        if (empty($this->objects)) {
            $this->parseObjects();
        }
        return $this->objects;
    }

    private function parseObjects(): void {
        $this->objects = [];
        foreach ($this->results as $result) {
            $uri             = $result->item->getUri();
            $qId             = substr($uri, strrpos($uri, '/') + 1);
            $this->objects[] = WikidataEntity::fetch($qId);
        }
    }
}
