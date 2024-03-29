<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

class StopoverFactory extends Factory
{
    public function definition(): array {
        return [
            'trip_id'                    => Trip::factory(),
            'train_station_id'           => Station::factory(),
            'arrival_planned'            => $this->faker->dateTimeBetween(),
            'arrival_real'               => null,
            'arrival_platform_planned'   => $this->faker->numberBetween(1, 99),
            'arrival_platform_real'      => $this->faker->numberBetween(1, 99),
            'departure_planned'          => $this->faker->dateTimeBetween(),
            'departure_real'             => null,
            'departure_platform_planned' => $this->faker->numberBetween(1, 99),
            'departure_platform_real'    => $this->faker->numberBetween(1, 99),
        ];
    }
}
