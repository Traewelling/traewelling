<?php

namespace Database\Seeders;

use App\Dto\Internal\CheckInRequestDto;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\Event;
use App\Models\StatusTag;
use App\Models\Trip;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;

class CheckinSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $user = User::inRandomOrder()->first();
            $trip = Trip::inRandomOrder()->first();

            $dto = new CheckInRequestDto();
            $dto->setUser($user)
                ->setTrip($trip)
                ->setOrigin($trip->originStation)// Checkin from the first station...
                ->setDeparture($trip->departure)
                ->setDestination($trip->destinationStation)// ...to the last station
                ->setArrival($trip->arrival)
                ->setEvent(random_int(0, 1) ? Event::inRandomOrder()->first() : null);

            try {
                $checkinResponse = TrainCheckinController::checkin($dto);
                $status = $checkinResponse->status;
                StatusTag::factory(['status_id' => $status->id])->create();
            } catch (Exception) {
                continue;
            }
        }
    }
}
