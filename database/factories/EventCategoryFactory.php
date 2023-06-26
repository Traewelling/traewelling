<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventCategoryFactory extends Factory
{
    public function definition(): array {
        return [
            'event_id' => Event::factory(),
            'category' => $this->faker->randomNumber(1, 3),
        ];
    }
}
