<?php

namespace App\Dto\GeoJson;

class Properties
{
    private array $properties;

    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    public function addProperty(string $key, mixed $value): void
    {
        $this->properties[$key] = $value;
    }

    public function toArray(): array
    {
        return $this->properties;
    }
}
