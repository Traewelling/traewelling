<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Polyline>
 */
class PolyLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hash'   => $this->faker->sha1,
            'source' => 'hafas',
        ];
    }
}
