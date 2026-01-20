<?php

namespace Database\Factories;

use App\Enum\TripSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class StationIdentifierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'relevance' => $this->faker->numberBetween(0, 100),
            'station_id' => $this->faker->numberBetween(1, 100),
            'identifier' => $this->faker->uuid,
            'type' => $this->faker->randomElement(['motis']),
            'origin' => $this->faker->randomElement([TripSource::TRANSITOUS]),
            'name' => $this->faker->city,
        ];
    }
}
