<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OperatorFactory extends Factory
{
    public function definition(): array
    {
        $companyName = $this->faker->company;

        return [
            'name' => $companyName,
        ];
    }
}
