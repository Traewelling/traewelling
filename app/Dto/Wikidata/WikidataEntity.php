<?php declare(strict_types=1);

namespace App\Dto\Wikidata;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use JsonException;

readonly class WikidataEntity
{

    public string $qId;
    public array  $rawData;

    /**
     * @param string $qId
     *
     * @return self
     * @throws JsonException|ModelNotFoundException
     */
    public static function fetch(string $qId): self {
        if (!str_starts_with($qId, 'Q')) {
            throw new \InvalidArgumentException('Invalid QID');
        }
        $instance      = new self();
        $instance->qId = $qId;
        $json          = json_decode(file_get_contents('https://www.wikidata.org/wiki/Special:EntityData/' . $qId . '.json'), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($json['entities'][$qId])) {
            throw new ModelNotFoundException('Entity not found');
        }

        $instance->rawData = $json['entities'][$qId];

        return $instance;
    }

    public function getLabels(): array {
        return $this->rawData['labels'] ?? [];
    }

    public function getLabel(string $language = 'en'): ?string {
        return $this->rawData['labels'][$language]['value'] ?? null;
    }

    public function getDescription(string $language = 'en'): ?string {
        return $this->rawData['descriptions'][$language]['value'] ?? null;
    }

    public function getClaims(string $property): array {
        return $this->rawData['claims'][$property] ?? [];
    }

}
