<?php

namespace Tests\Feature\Transport;

use App\Models\TrainCheckin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinDurationTest extends TestCase
{

    use RefreshDatabase;

    public function testCheckinDurationWithoutAnyAdditionalTimings(): void {
        $checkin = TrainCheckin::factory([
                                             'departure' => '2021-01-01T00:00:00+01:00',
                                             'arrival'   => '2021-01-01T01:00:00+01:00',
                                         ])->create();
        $this->assertEquals(60, $checkin->duration);
    }

    public function testCheckinDurationWithDelayedDeparture(): void {
        $checkin = TrainCheckin::factory([
                                             'departure' => '2021-01-01T00:00:00+01:00',
                                             'arrival'   => '2021-01-01T01:00:00+01:00',
                                         ])->create();

        $checkin->origin_stopover->departure_real = '2021-01-01T00:10:00+01:00';

        $this->assertEquals(50, $checkin->duration);
    }

    public function testCheckinDurationWithDelayedArrival(): void {
        $checkin = TrainCheckin::factory([
                                             'departure' => '2021-01-01T00:00:00+01:00',
                                             'arrival'   => '2021-01-01T01:00:00+01:00',
                                         ])->create();

        $checkin->destination_stopover->arrival_real = '2021-01-01T01:10:00+01:00';

        $this->assertEquals(70, $checkin->duration);
    }

    public function testCheckinDurationWithDelayedDepartureAndArrival(): void {
        $checkin = TrainCheckin::factory([
                                             'departure' => '2021-01-01T00:00:00+01:00',
                                             'arrival'   => '2021-01-01T01:00:00+01:00',
                                         ])->create();

        $checkin->origin_stopover->departure_real    = '2021-01-01T00:10:00+01:00';
        $checkin->destination_stopover->arrival_real = '2021-01-01T01:10:00+01:00';

        $this->assertEquals(60, $checkin->duration);
    }

    public function testCheckinDurationWithManualDeparture(): void {
        $checkin = TrainCheckin::factory([
                                             'departure'      => '2021-01-01T00:00:00+01:00',
                                             'real_departure' => '2021-01-01T00:10:00+01:00',
                                             'arrival'        => '2021-01-01T01:00:00+01:00',
                                         ])->create();

        $this->assertEquals(50, $checkin->duration);
    }

    public function testCheckinDurationWithManualArrival(): void {
        $checkin = TrainCheckin::factory([
                                             'departure'    => '2021-01-01T00:00:00+01:00',
                                             'arrival'      => '2021-01-01T01:00:00+01:00',
                                             'real_arrival' => '2021-01-01T01:10:00+01:00',
                                         ])->create();

        $this->assertEquals(70, $checkin->duration);
    }

    public function testCheckinDurationWithManualDepartureAndArrival(): void {
        $checkin = TrainCheckin::factory([
                                             'departure'      => '2021-01-01T00:00:00+01:00',
                                             'real_departure' => '2021-01-01T00:05:00+01:00',
                                             'arrival'        => '2021-01-01T01:00:00+01:00',
                                             'real_arrival'   => '2021-01-01T01:10:00+01:00',
                                         ])->create();

        $this->assertEquals(65, $checkin->duration);
    }

    public function testCheckinDurationWithManualDepartureAndArrivalAndGeneralDelayedDeparture(): void {
        $checkin = TrainCheckin::factory([
                                             'departure'      => '2021-01-01T00:00:00+01:00',
                                             'real_departure' => '2021-01-01T00:05:00+01:00',
                                             'arrival'        => '2021-01-01T01:00:00+01:00',
                                             'real_arrival'   => '2021-01-01T01:10:00+01:00',
                                         ])->create();

        //This should be ignored, because the real departure is already set by user
        $checkin->origin_stopover->departure_real = '2021-01-01T00:30:00+01:00';

        $this->assertEquals(65, $checkin->duration);
    }


    public function testCheckinDurationWithManualDepartureAndArrivalAndGeneralDelayedArrival(): void {
        $checkin = TrainCheckin::factory([
                                             'departure'      => '2021-01-01T00:00:00+01:00',
                                             'real_departure' => '2021-01-01T00:05:00+01:00',
                                             'arrival'        => '2021-01-01T01:00:00+01:00',
                                             'real_arrival'   => '2021-01-01T01:10:00+01:00',
                                         ])->create();

        //This should be ignored, because the real arrival is already set by user
        $checkin->destination_stopover->arrival_real = '2021-01-01T01:50:00+01:00';

        $this->assertEquals(65, $checkin->duration);
    }
}
