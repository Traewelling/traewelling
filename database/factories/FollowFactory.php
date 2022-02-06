<?php

namespace Database\Factories;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowFactory extends Factory
{

    protected $model = Follow::class;

    public function definition(): array {
        return [
            'user_id'   => User::factory(),
            'follow_id' => User::factory(),
        ];
    }
}
