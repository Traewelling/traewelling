<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['info', 'warning', 'danger', 'success']),
            'active_from' => now()->subDay(),
            'active_until' => now()->addWeek(),
            'url' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active_from' => now()->addDays(10), 'active_until' => now()->addDays(20)]);
    }
}
