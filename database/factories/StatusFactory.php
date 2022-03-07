<?php

namespace Database\Factories;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFactory extends Factory
{

    protected $model = Status::class;

    public function definition(): array {
        return [
            'body'       => $this->faker->paragraph,
            'user_id'    => User::factory(),
            'business'   => $this->faker->randomElement(Business::cases())->value,
            'visibility' => StatusVisibility::PUBLIC,
            'type'       => 'hafas',
            'event_id'   => null,
        ];
    }
}
