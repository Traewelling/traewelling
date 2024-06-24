<?php

namespace Database\Factories;

use App\Enum\StatusTagKey;
use App\Enum\StatusVisibility;
use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusTagFactory extends Factory
{

    public function definition(): array {
        return [
            'status_id'  => Status::factory(),
            'key'        => $this->faker->randomElement([
                                                            StatusTagKey::ROLE->value,
                                                            StatusTagKey::SEAT->value,
                                                            StatusTagKey::TICKET->value,
                                                            StatusTagKey::TRAVEL_CLASS->value,
                                                            StatusTagKey::WAGON->value,
                                                            StatusTagKey::WAGON_CLASS->value,
                                                            StatusTagKey::LOCOMOTIVE_CLASS->value,
                                                        ]),
            'value'      => $this->faker->word,
            'visibility' => StatusVisibility::PUBLIC->value,
        ];
    }
}
