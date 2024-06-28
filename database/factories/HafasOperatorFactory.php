<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HafasOperatorFactory extends Factory
{
    public function definition(): array {
        $companyName = $this->faker->company;
        return [
            'hafas_id' => Str::slug($companyName . $this->faker->hexColor, '_'),
            'name'     => $companyName,
        ];
    }
}
