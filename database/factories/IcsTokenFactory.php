<?php

namespace Database\Factories;

use App\Models\IcsToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class IcsTokenFactory extends Factory
{

    protected $model = IcsToken::class;

    #[ArrayShape([
        'user_id' => "\Illuminate\Database\Eloquent\Factories\Factory",
        'token'   => "string"
    ])]
    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'token'   => $this->faker->unique()->uuid
        ];
    }
}
