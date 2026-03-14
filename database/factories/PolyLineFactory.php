<?php

namespace Database\Factories;

use App\Models\Polyline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Polyline>
 */
class PolyLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hash' => $this->faker->sha1,
            'source' => 'hafas',
        ];
    }
}
