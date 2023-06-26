<?php

namespace Database\Factories;

use App\Models\TrainStation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array {
        return [
            'name'        => $this->faker->word,
            'hashtag'     => $this->faker->word,
            'slug'        => $this->faker->unique()->slug,
            'host'        => $this->faker->company,
            'url'         => $this->faker->url,
            'begin'       => Carbon::now()->subDays(3)->toIso8601String(),
            'end'         => Carbon::now()->addDays(3)->toIso8601String(),
            'event_start' => Carbon::now()->subDays(3)->toIso8601String(),
            'event_end'   => Carbon::now()->addDays(3)->toIso8601String(),
            'station_id'  => TrainStation::factory(),
        ];
    }
}
