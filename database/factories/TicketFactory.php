<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $validFrom = Carbon::now()->startOfMonth();

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'valid_from' => $validFrom->toDateString(),
            'valid_until' => $validFrom->copy()->endOfMonth()->toDateString(),
            'price' => $this->faker->randomFloat(2, 1, 500),
            'currency' => 'EUR',
        ];
    }
}
