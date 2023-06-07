<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array {
        return [
            'username'          => $this->faker->unique()->userName,
            'name'              => $this->faker->name,
            'email'             => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'privacy_ack_at'    => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
            'private_profile'   => false,
            'likes_enabled'     => true,
            'role'              => 0,
        ];
    }
}
