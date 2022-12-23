<?php

namespace Database\Factories;

use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusTagFactory extends Factory
{

    public function definition() {
        return [
            'status_id' => Status::factory(),
            'key'       => $this->faker->word,
            'value'     => $this->faker->word,
        ];
    }
}
