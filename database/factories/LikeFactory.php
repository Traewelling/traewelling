<?php

namespace Database\Factories;

use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LikeFactory extends Factory
{
    public function definition(): array {
        return [
            'user_id'   => User::factory(),
            'status_id' => Status::factory(),
        ];
    }
}
