<?php

namespace Database\Factories;

use App\Faker\StationFaker;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\StationIdentifierType;
use Illuminate\Database\Eloquent\Factories\Factory;

class StationFactory extends Factory
{
    public function definition(): array
    {
        $this->faker->addProvider(new StationFaker($this->faker));

        /** @var StationFaker $station */
        $station = $this->faker->unique()->station();

        return [
            'name' => $station['name'],
            'latitude' => $station['latitude'],
            'longitude' => $station['longitude'],
            'time_offset' => null,
            'shift_time' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Station $stationModel) {
            $this->faker->addProvider(new StationFaker($this->faker));
            /** @var StationFaker $station */
            $station = $this->faker->station();

            // Create station identifiers
            if (!empty($station['ibnr'])) {
                StationIdentifier::create([
                    'station_id' => $stationModel->id,
                    'type' => StationIdentifierType::DE_DB_IBNR,
                    'identifier' => (string) $station['ibnr'],
                    'name' => $stationModel->name,
                    'origin' => 'factory',
                ]);
            }

            if (!empty($station['wikidata_id'])) {
                StationIdentifier::create([
                    'station_id' => $stationModel->id,
                    'type' => StationIdentifierType::WIKIDATA_ID,
                    'identifier' => $station['wikidata_id'],
                    'name' => $stationModel->name,
                    'origin' => 'factory',
                ]);
            }

            if (!empty($station['rl100'])) {
                StationIdentifier::create([
                    'station_id' => $stationModel->id,
                    'type' => StationIdentifierType::DE_DB_RIL100,
                    'identifier' => $station['rl100'],
                    'name' => $stationModel->name,
                    'origin' => 'factory',
                ]);
            }

            if (!empty($station['ifopt'])) {
                StationIdentifier::create([
                    'station_id' => $stationModel->id,
                    'type' => StationIdentifierType::DE_DB_IFOPT,
                    'identifier' => $station['ifopt'],
                    'name' => $stationModel->name,
                    'origin' => 'factory',
                ]);
            }
        });
    }
}
