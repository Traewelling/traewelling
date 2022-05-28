<?php

namespace Database\Factories;

use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainCheckinFactory extends Factory
{
    public function definition(): array {
        return [
            'status_id'   => Status::factory(),
            'user_id'     => User::factory(),
            'trip_id'     => HafasTrip::factory()->create()->trip_id,
            'origin'      => TrainStation::factory()->create()->ibnr,
            'destination' => TrainStation::factory()->create()->ibnr,
            'distance'    => $this->faker->randomFloat(2, 0, 100),
            'departure'   => $this->faker->dateTimeBetween('-1 year', '+1 year'),
            'arrival'     => $this->faker->dateTimeBetween('-1 year', '+1 year'),
            'points'      => $this->faker->numberBetween(0, 100),
        ];
    }
}
