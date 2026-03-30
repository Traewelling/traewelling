<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivacyPolicyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'body_md_de' => "# Allgemeiner Hinweis und Pflicht&shy;informationen\n" . $this->faker->paragraphs(3, true),
            'body_md_en' => "# General notes and mandatory information\n" . $this->faker->paragraphs(3, true),
            'valid_at' => Carbon::now()->subYear()->toIso8601String(),
        ];
    }
}
