<?php

namespace App\Http\Controllers\Backend\Transport\dtos;

use Illuminate\Support\Collection;

class StationDto
{
    public int $id;

    public ?int $ibnr;

    public string $name;

    public float $latitude;

    public float $longitude;

    public ?string $rilIdentifier;

    public Collection $areas;

    public function __construct(
        int $id,
        ?int $ibnr,
        string $name,
        float $latitude,
        float $longitude,
        ?string $rilIdentifier = null,
        ?Collection $areas = null
    ) {
        $this->id = $id;
        $this->ibnr = $ibnr;
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->rilIdentifier = $rilIdentifier;
        $this->areas = $areas ?? new Collection();
    }

    // this is needed to emulate the Eloquent relationLoaded method
    public function relationLoaded(string $relation): bool
    {
        return $relation === 'areas' && $this->areas->isNotEmpty();
    }
}
