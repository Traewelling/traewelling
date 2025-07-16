<?php

namespace App\Dto\Internal;

use App\Models\MotisSourceLicense;
use Illuminate\Support\Collection;

readonly class FilteredDepartures
{
    /** @var Collection|Departure[] */
    public Collection|array $departures;
    /** @var Collection|array */
    public Collection|array $removedEntries;
    public int $removedCount;

    public function __construct(
        Collection|array $departures,
        Collection|array $removedEntries,
        int $removedCount = 0,
    ) {
        $this->departures   = $departures;
        $this->removedEntries = $removedEntries;
        $this->removedCount = $removedCount;
    }
}
