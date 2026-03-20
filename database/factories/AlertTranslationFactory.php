<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Alert;
use App\Models\AlertTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlertTranslation>
 */
class AlertTranslationFactory extends Factory
{
    protected $model = AlertTranslation::class;

    public function definition(): array
    {
        return [
            'alert_id' => Alert::factory(),
            'locale' => $this->faker->randomElement(['de', 'en']),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->sentence(10),
            'url' => null,
        ];
    }
}
