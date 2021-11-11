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
            'business'   => $this->faker->randomElement(Business::getList()),
            'event_id'   => null,
            'visibility' => $this->faker->randomElement(StatusVisibility::getList()),
        ];
    }
}
