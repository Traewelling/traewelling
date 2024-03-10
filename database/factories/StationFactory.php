<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StationFactory extends Factory
{
    public function definition(): array {
        return [
            'ibnr'          => $this->faker->unique()->numberBetween(8000001, 8999999),
            'wikidata_id'   => null,
            'ifopt_a'       => $this->faker->randomElement(['de', 'at', 'ch', 'fr']),
            'ifopt_b'       => $this->faker->numberBetween(10000, 99999),
            'ifopt_c'       => $this->faker->numberBetween(1, 9999),
            'ifopt_d'       => $this->faker->boolean() ? $this->faker->numberBetween(1, 9999) : null,
            'ifopt_e'       => $this->faker->boolean() ? $this->faker->numberBetween(1, 9) : null,
            'rilIdentifier' => substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4),
            'name'          => $this->faker->unique()->city,
            'latitude'      => $this->faker->latitude,
            'longitude'     => $this->faker->longitude,
            'time_offset'   => null,
            'shift_time'    => null,
        ];
    }
}
