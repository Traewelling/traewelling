<?php

namespace Database\Factories;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFactory extends Factory
{
    public function definition(): array {
        return [
            'body'       => $this->faker->paragraph,
            'user_id'    => User::factory(),
            'business'   => $this->faker->randomElement(Business::cases())->value, //TODO: rename to travelReason
            'visibility' => StatusVisibility::PUBLIC,
            'event_id'   => null,
        ];
    }
}
