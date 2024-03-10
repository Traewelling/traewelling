<?php

namespace Tests\Feature\Status;

use App\Enum\TimeType;
use App\Models\Checkin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeTypeTest extends TestCase
{
    use RefreshDatabase;

    public static function getTimeTypeFindPreference(): array {
        return [
            [false, false, TimeType::PLANNED],
            [false, true, TimeType::REALTIME],
            [true, false, TimeType::MANUAL],
            [true, true, TimeType::MANUAL],
        ];
    }

    /**
     * @dataProvider getTimeTypeFindPreference
     */
    public function testTimeTypeFindPreference(bool $manual, bool $delay, TimeType $expected): void {
        // GIVEN
        $checkin = Checkin::factory()->create();

        $checkin->originStopover->update(['departure_real' => null]);

        // WHEN
        if ($manual) $this->setManualDeparture($checkin, 8);
        if ($delay) $this->setDelayedTrainDeparture($checkin, 5);

        // THEN
        $this->assertEquals($expected, $checkin->displayDeparture->type);

        if ($manual || $delay) {
            $this->assertNotNull($checkin->displayDeparture->original);

        }
    }

    public function testSameManualDeparture(): void {
        // GIVEN
        $checkin = Checkin::factory()->create();

        $checkin->originStopover->update(['departure_real' => null]);

        // WHEN
        $this->setManualDeparture($checkin, 0);

        // THEN
        $this->assertEquals(TimeType::MANUAL, $checkin->displayDeparture->type);
        $this->assertNull($checkin->displayDeparture->original);
    }

    public function testSameRealTimeDeparture(): void {
        // GIVEN
        $checkin = Checkin::factory()->create();

        $checkin->originStopover->update(['departure_real' => null]);

        // WHEN
        $this->setDelayedTrainDeparture($checkin, 0);

        // THEN
        $this->assertEquals(TimeType::REALTIME, $checkin->displayDeparture->type);
        $this->assertNull($checkin->displayDeparture->original);
    }

    private function setDelayedTrainDeparture(Checkin $checkin, int $min): void {
        $checkin->originStopover->update([
                                                     'departure_real' => $checkin->originStopover->departure_planned
                                                         ->copy()
                                                         ->addMinutes($min)
                                                 ]);
    }

    private function setManualDeparture(Checkin $checkin, int $min): void {
        $checkin->update([
                             'manual_departure' => $checkin->departure->copy()->addMinutes($min)
                         ]);
    }
}
