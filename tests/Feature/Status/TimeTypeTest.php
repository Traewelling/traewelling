<?php

namespace Tests\Feature\Status;

use App\Enum\TimeType;
use App\Models\TrainCheckin;
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
        $checkin = TrainCheckin::factory()->create();

        $checkin->origin_stopover->departure_real = null;
        $checkin->origin_stopover->update();

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
        $checkin = TrainCheckin::factory()->create();

        $checkin->origin_stopover->departure_real = null;
        $checkin->origin_stopover->update();

        // WHEN
        $this->setManualDeparture($checkin, 0);

        // THEN
        $this->assertEquals(TimeType::MANUAL, $checkin->displayDeparture->type);
        $this->assertNull($checkin->displayDeparture->original);
    }

    public function testSameRealTimeDeparture(): void {
        // GIVEN
        $checkin = TrainCheckin::factory()->create();

        $checkin->origin_stopover->departure_real = null;
        $checkin->origin_stopover->update();

        // WHEN
        $this->setDelayedTrainDeparture($checkin, 0);

        // THEN
        $this->assertEquals(TimeType::REALTIME, $checkin->displayDeparture->type);
        $this->assertNull($checkin->displayDeparture->original);
    }

    private function setDelayedTrainDeparture($checkin, int $min) {
        $checkin->origin_stopover->departure_real = $checkin->origin_stopover
            ->departure_planned
            ->copy()
            ->addMinutes($min);
        $checkin->origin_stopover->update();
    }

    private function setManualDeparture($checkin, int $min) {
        $checkin->manual_departure = $checkin->departure
            ->copy()
            ->addMinutes($min);
        $checkin->update();
    }
}
