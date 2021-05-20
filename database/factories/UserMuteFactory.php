<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserMute;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class UserMuteFactory extends Factory
{

    protected $model = UserMute::class;

    #[ArrayShape([
        'user_id'  => "\Illuminate\Database\Eloquent\Factories\Factory",
        'muted_id' => "\Illuminate\Database\Eloquent\Factories\Factory"
    ])]
    public function definition(): array {
        return [
            'user_id'  => User::factory(),
            'muted_id' => User::factory(),
        ];
    }
}
