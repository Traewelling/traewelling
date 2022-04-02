<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RemarkFactory extends Factory
{

    public function definition(): array {
        return [
            'text'    => $this->faker->sentence,
            'type'    => $this->faker->unique()->word,
            'code'    => $this->faker->unique()->word,
            'summary' => $this->faker->sentence,
        ];
    }
}
