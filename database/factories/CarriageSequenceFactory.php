<?php

namespace Database\Factories;

use App\Models\TrainStopover;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarriageSequenceFactory extends Factory
{

    public function definition(): array {
        return [
            'stopover_id'    => TrainStopover::factory(),
            'position'       => $this->faker->unique()->numberBetween(),
            'sequence'       => $this->faker->randomLetter,
            'vehicle_type'   => $this->faker->word,
            'vehicle_number' => $this->faker->numberBetween(),
            'order_number'   => $this->faker->numberBetween(1, 99),
        ];
    }
}
