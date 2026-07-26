<?php

declare(strict_types=1);

namespace App\Services\Transport;

class ExcludedSourceService
{
    /**
     * Excluded feeds that reuse another feed's stop ids verbatim, mapped onto that feed.
     * MOTIS deduplicates stop clusters in its geocoder and may return the excluded feed as the
     * only representative of a stop we already know through an allowed feed, so dropping the
     * result outright loses the stop. de-amarillo-bw covers Baden-Wuerttemberg and uses the same
     * DELFI stop ids, e.g. "de-amarillo-bw_de:08212:1002" -> "de-DELFI_de:08212:1002".
     *
     * @var array<string, string>
     */
    private const SOURCE_REPLACEMENTS = [
        'de-amarillo-bw' => 'de-DELFI',
    ];

    /**
     * Whether the given MOTIS stop/station identifier belongs to a transit data source that is
     * excluded from Träwelling. MOTIS identifiers are prefixed with the feed name, e.g.
     * "de-amarillo-bw_de:08221:1160", so an excluded source matches every identifier starting
     * with "{source}_".
     */
    public function isExcluded(?string $motisIdentifier): bool
    {
        if ($motisIdentifier === null || $motisIdentifier === '') {
            return false;
        }

        foreach ($this->excludedSources() as $source) {
            if (str_starts_with($motisIdentifier, $source . '_')) {
                return true;
            }
        }

        return false;
    }

    /**
     * The equivalent identifier of an allowed source for an excluded identifier, or null if the
     * excluded source has no known equivalent and the result has to be dropped.
     */
    public function resolveReplacement(?string $motisIdentifier): ?string
    {
        if ($motisIdentifier === null || $motisIdentifier === '') {
            return null;
        }

        foreach (self::SOURCE_REPLACEMENTS as $source => $replacement) {
            if (str_starts_with($motisIdentifier, $source . '_')) {
                return $replacement . substr($motisIdentifier, strlen($source));
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function excludedSources(): array
    {
        return array_filter(config('trwl.motis.excluded_sources', []));
    }
}
