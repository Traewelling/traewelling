<?php

namespace Database\Factories;

use App\Models\IcsToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class IcsTokenFactory extends Factory
{
    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'token'   => $this->faker->unique()->uuid
        ];
    }
}
