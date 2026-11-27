<?php

declare(strict_types=1);

namespace App\Dto\Wikidata;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JsonException;

readonly class WikidataEntity
{
    private function __construct(
        public string $qId,
        public array $rawData,
    ) {}

    /**
     * @throws JsonException|ModelNotFoundException|ConnectionException
     */
    public static function fetch(string $qId): self
    {
        if (!str_starts_with($qId, 'Q')) {
            throw new \InvalidArgumentException('Invalid QID');
        }

        $json = Http::withUserAgent(config('services.user_agent'))
            ->get('https://www.wikidata.org/wiki/Special:EntityData/' . $qId . '.json')
            ->json();
        if (!isset($json['entities'][$qId])) {
            throw new ModelNotFoundException('Entity not found');
        }

        return new self(
            qId: $qId,
            rawData: $json['entities'][$qId],
        );
    }

    public function getLabel(string $language = 'en'): ?string
    {
        return $this->rawData['labels'][$language]['value'] ?? null;
    }

    public function getDescription(string $language = 'en'): ?string
    {
        return $this->rawData['descriptions'][$language]['value'] ?? null;
    }

    public function getClaims(string $property): array
    {
        return $this->rawData['claims'][$property] ?? [];
    }
}
