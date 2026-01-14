<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserMuteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'muted_id' => User::factory(),
        ];
    }
}
