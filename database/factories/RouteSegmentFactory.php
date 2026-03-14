<?php

namespace Database\Factories;

use App\Models\RouteSegment;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RouteSegment>
 */
class RouteSegmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'from_station_id' => Station::inRandomOrder()->first()->id,
            'to_station_id' => Station::inRandomOrder()->first()->id,
            'distance' => $this->faker->numberBetween(1000, 100000),
            'duration' => $this->faker->numberBetween(600, 7200),
            'polyline' => '_p~iF~ps|U_ulLnnqC_mqNvxq`@',
            'polyline_precision' => 5,
            'path_type' => 'driving',
        ];
    }
}
