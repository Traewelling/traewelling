<?php

namespace Database\Factories;

use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopOver;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainStopOverFactory extends Factory
{
    protected $model = TrainStopOver::class;

    public function definition(): array {
        return [
            'trip_id'                    => HafasTrip::factory(),
            'train_station_id'           => TrainStation::factory(),
            'arrival_planned'            => $this->faker->dateTimeBetween(),
            'arrival_real'               => $this->faker->dateTimeBetween(),
            'arrival_platform_planned'   => $this->faker->numberBetween(1, 99),
            'arrival_platform_real'      => $this->faker->numberBetween(1, 99),
            'departure_planned'          => $this->faker->dateTimeBetween(),
            'departure_real'             => $this->faker->dateTimeBetween(),
            'departure_platform_planned' => $this->faker->numberBetween(1, 99),
            'departure_platform_real'    => $this->faker->numberBetween(1, 99),
        ];
    }
}
