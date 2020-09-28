<?php

namespace Database\Factories;

use App\Models\TrainStation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainStationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrainStation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            'ibnr'      => $this->faker->unique()->numberBetween(8000001, 8999999),
            'name'      => $this->faker->unique()->city,
            'latitude'  => $this->faker->latitude,
            'longitude' => $this->faker->longitude
        ];
    }
}
