<?php

namespace Tests\Feature\Status;

use App\Enum\TimeType;
use App\Models\TrainCheckin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider manualTimeProvider
     */
    public function testManualTime(bool $targetArrival, ?int $manualDeltaMins, ?bool $shouldHaveOriginal, bool $shouldBeTypeManual) {
        $checkin       = TrainCheckin::factory()->create();

        if (isset($manualDeltaMins)) {
            if ($targetArrival) {
                $edit_time = $checkin->destination_stopover->arrival_planned->copy();
                $checkin->manual_arrival = $edit_time->addMinutes($manualDeltaMins);
            } else {
                $edit_time = $checkin->origin_stopover->departure_planned->copy();
                $checkin->manual_departure = $edit_time->addMinutes($manualDeltaMins);
            }
            $checkin->update();
        }

        if ($targetArrival) {
            $display_time = $checkin->displayArrival;
        } else {
            $display_time = $checkin->displayDeparture;
        }

        if (isset($shouldHaveOriginal)) {
            $this->assertTrue($shouldHaveOriginal === isset($display_time->original));
        }
        $this->assertTrue($shouldBeTypeManual === ($display_time->type === TimeType::MANUAL));
    }

    public static function manualTimeProvider() {
        $testData = [];
        foreach ([false, true] as $b) {
            $testData = array_merge($testData, [
                [
                    $b, null, null, false
                ],
                [
                    $b, 0, false, true
                ],
                [
                    $b, 2, true, true
                ],
                [
                    $b, -1, true, true
                ],
            ]);
        }
        return $testData;
    }
}
