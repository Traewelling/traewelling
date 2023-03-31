<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserReportFactory extends Factory
{

    public function definition(): array {
        return [
            'reporter_id' => User::factory(),
            'user_id'     => User::factory(),
            'message'     => $this->faker->text,
        ];
    }
}
