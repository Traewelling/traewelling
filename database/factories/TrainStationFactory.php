<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TrainStationFactory extends Factory
{
    public function definition(): array {
        return [
            'ibnr'          => $this->faker->unique()->numberBetween(8000001, 8999999),
            'name'          => $this->faker->unique()->city,
            'latitude'      => $this->faker->latitude,
            'longitude'     => $this->faker->longitude,
            'rilIdentifier' => substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4),
        ];
    }
}
