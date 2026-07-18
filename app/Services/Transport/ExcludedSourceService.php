<?php

declare(strict_types=1);

namespace App\Services\Transport;

class ExcludedSourceService
{
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
     * @return array<int, string>
     */
    private function excludedSources(): array
    {
        return array_filter(config('trwl.motis.excluded_sources', []));
    }
}
