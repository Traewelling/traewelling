<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Faker\StationFaker;
use Illuminate\Database\Eloquent\Factories\Factory;

class StationFactory extends Factory
{
    public function definition(): array
    {
        $this->faker->addProvider(new StationFaker($this->faker));

        /** @var array $station */
        $station = $this->faker->unique()->station();

        return [
            'name' => $station['name'],
            'latitude' => $station['latitude'],
            'longitude' => $station['longitude'],
            'time_offset' => null,
            'shift_time' => null,
        ];
    }
}
