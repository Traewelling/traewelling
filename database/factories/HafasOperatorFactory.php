<?php

namespace Database\Factories;

use App\Models\HafasOperator;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class HafasOperatorFactory extends Factory
{

    protected $model = HafasOperator::class;

    #[ArrayShape([
        'hafas_id' => "string",
        'name'     => "string"
    ])]
    public function definition(): array {
        return [
            'hafas_id' => $this->faker->unique()->word,
            'name'     => $this->faker->company
        ];
    }
}
