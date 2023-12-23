<?php

namespace Database\Factories;

use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventSuggestionFactory extends Factory
{
    public function definition(): array {
        $begin = Carbon::today()->addDays(rand(0, 30));
        $end   = $begin->clone()->addDays(3);

        return [
            'user_id'    => User::factory(),
            'name'       => $this->faker->word,
            'host'       => $this->faker->company,
            'url'        => $this->faker->url,
            'station_id' => Station::factory(),
            'begin'      => $begin->toIso8601String(),
            'end'        => $end->toIso8601String(),
        ];
    }
}
