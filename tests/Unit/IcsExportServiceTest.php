<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Dto\Internal\IcsExportStatus;
use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IcsExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ics_uses_planned_times_when_no_real_or_manual(): void
    {
        $user = User::factory()->create();
        $departure = Carbon::parse('2025-06-01 08:00:00', 'UTC');
        $arrival = Carbon::parse('2025-06-01 10:00:00', 'UTC');

        $checkin = Checkin::factory(['user_id' => $user->id])->create([
            'departure' => $departure,
            'arrival' => $arrival,
            'manual_departure' => null,
            'manual_arrival' => null,
        ]);
        $checkin->originStopover->update(['departure_planned' => $departure, 'departure_real' => null]);
        $checkin->destinationStopover->update(['arrival_planned' => $arrival, 'arrival_real' => null]);
        $checkin->refresh();

        $dto = IcsExportStatus::fromCheckin($checkin);

        $this->assertEquals($departure->toIso8601ZuluString(), $dto->departure);
        $this->assertEquals($arrival->toIso8601ZuluString(), $dto->arrival);
    }

    public function test_ics_prefers_real_times_over_planned_times(): void
    {
        $user = User::factory()->create();
        $plannedDeparture = Carbon::parse('2025-06-01 08:00:00', 'UTC');
        $realDeparture = Carbon::parse('2025-06-01 08:07:00', 'UTC');
        $plannedArrival = Carbon::parse('2025-06-01 10:00:00', 'UTC');
        $realArrival = Carbon::parse('2025-06-01 10:14:00', 'UTC');

        $checkin = Checkin::factory(['user_id' => $user->id])->create([
            'departure' => $plannedDeparture,
            'arrival' => $plannedArrival,
            'manual_departure' => null,
            'manual_arrival' => null,
        ]);
        $checkin->originStopover->update(['departure_planned' => $plannedDeparture, 'departure_real' => $realDeparture]);
        $checkin->destinationStopover->update(['arrival_planned' => $plannedArrival, 'arrival_real' => $realArrival]);
        $checkin->refresh();

        $dto = IcsExportStatus::fromCheckin($checkin);

        $this->assertEquals($realDeparture->toIso8601ZuluString(), $dto->departure);
        $this->assertEquals($realArrival->toIso8601ZuluString(), $dto->arrival);
        $this->assertNotEquals($plannedDeparture->toIso8601ZuluString(), $dto->departure);
        $this->assertNotEquals($plannedArrival->toIso8601ZuluString(), $dto->arrival);
    }

    public function test_ics_prefers_manual_times_over_real_and_planned(): void
    {
        $user = User::factory()->create();
        $plannedDeparture = Carbon::parse('2025-06-01 08:00:00', 'UTC');
        $realDeparture = Carbon::parse('2025-06-01 08:07:00', 'UTC');
        $manualDeparture = Carbon::parse('2025-06-01 07:45:00', 'UTC');
        $plannedArrival = Carbon::parse('2025-06-01 10:00:00', 'UTC');
        $realArrival = Carbon::parse('2025-06-01 10:14:00', 'UTC');
        $manualArrival = Carbon::parse('2025-06-01 10:30:00', 'UTC');

        $checkin = Checkin::factory(['user_id' => $user->id])->create([
            'departure' => $plannedDeparture,
            'arrival' => $plannedArrival,
            'manual_departure' => $manualDeparture,
            'manual_arrival' => $manualArrival,
        ]);
        $checkin->originStopover->update(['departure_planned' => $plannedDeparture, 'departure_real' => $realDeparture]);
        $checkin->destinationStopover->update(['arrival_planned' => $plannedArrival, 'arrival_real' => $realArrival]);
        $checkin->refresh();

        $dto = IcsExportStatus::fromCheckin($checkin);

        $this->assertEquals($manualDeparture->toIso8601ZuluString(), $dto->departure);
        $this->assertEquals($manualArrival->toIso8601ZuluString(), $dto->arrival);
        $this->assertNotEquals($plannedDeparture->toIso8601ZuluString(), $dto->departure);
        $this->assertNotEquals($realDeparture->toIso8601ZuluString(), $dto->departure);
    }
}
