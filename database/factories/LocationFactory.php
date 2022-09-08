<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    public function definition(): array {
        return [
            'name'           => 'DB Lounge ' . ($this->faker->boolean ? 'Premium ' : '') . $this->faker->city,
            'address_street' => $this->faker->streetAddress,
            'address_zip'    => $this->faker->postcode,
            'address_city'   => $this->faker->city,
            'latitude'       => $this->faker->latitude(48.0, 51.0),
            'longitude'      => $this->faker->longitude(9.0, 10.0),
        ];
    }
}
