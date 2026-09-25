<?php

namespace App\Dto\Internal;

use App\Enum\MotisCategory;
use Illuminate\Support\Collection;

readonly class FilteredDepartures
{
    /** @var Collection|Departure[] */
    public Collection|array $departures;

    public Collection|array $removedEntries;

    public int $removedCount;

    /** @var MotisCategory[] modes serving the queried stop, regardless of the requested travel type */
    public array $availableModes;

    /**
     * @param  MotisCategory[]  $availableModes
     */
    public function __construct(
        Collection|array $departures,
        Collection|array $removedEntries,
        int $removedCount = 0,
        array $availableModes = [],
    ) {
        $this->departures = $departures;
        $this->removedEntries = $removedEntries;
        $this->removedCount = $removedCount;
        $this->availableModes = $availableModes;
    }

    /**
     * @param  MotisCategory[]  $availableModes
     */
    public function withAvailableModes(array $availableModes): self
    {
        return new self($this->departures, $this->removedEntries, $this->removedCount, $availableModes);
    }
}
