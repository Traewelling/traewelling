<?php

namespace Database\Factories;

use App\Models\HafasOperator;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class HafasOperatorFactory extends Factory
{
    public function definition(): array {
        return [
            'hafas_id' => $this->faker->hexColor,
            'name'     => $this->faker->company
        ];
    }
}
