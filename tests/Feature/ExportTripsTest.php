<?php

namespace Tests\Feature;

use App\Models\User;
use DateTime;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\TransportController;

class ExportTripsTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void {
        parent::setUp();

        $this->user = User::factory()->create();
        $response   = $this->actingAs($this->user)
                           ->post('/gdpr-ack');
        $response->assertStatus(302);
        $response->assertRedirect('/');

        $this->checkin("Frankfurt(M) Flughafen Fernbf", "8070003", new DateTime("+1 day 8:00"));
        $this->checkin("Essen Hbf", "8000098", new DateTime("+2 day 7:30"));
    }

    /**
     * This is mostly copied from Checkin Test
     * @param $stationName
     * @param $ibnr
     * @param DateTime $now
     */
    protected function checkin($stationName, $ibnr, DateTime $now) {
        $trainStationboard = TransportController::TrainStationboard($stationName, $now->format('U'), 'nationalExpress');
        $countDepartures   = count($trainStationboard['departures']);
        if ($countDepartures == 0) {
            $this->markTestSkipped("Unable to find matching trains. Is it night in $stationName?");
            return;
        }

        // Second: We don't like broken or cancelled trains.
        $i = 0;
        while ((isset($trainStationboard['departures'][$i]->cancelled)
                && $trainStationboard['departures'][$i]->cancelled)
            || count($trainStationboard['departures'][$i]->remarks) != 0) {
            $i++;
            if ($i == $countDepartures) {
                $this->markTestSkipped("Unable to find unbroken train. Is it stormy in $stationName?");
                return;
            }
        }
        $departure = $trainStationboard['departures'][$i];
        CheckinTest::isCorrectHafasTrip($departure, $now);

        // Third: Get the trip information
        $trip = TransportController::TrainTrip(
            $departure->tripId,
            $departure->line->name,
            $departure->stop->location->id
        );

        // WHEN: User tries to check-in
        $this->actingAs($this->user)
             ->post(route('trains.checkin'), [
                 'body'        => 'Example Body',
                 'tripID'      => $departure->tripId,
                 'start'       => $ibnr,
                 'destination' => $trip['stopovers'][0]['stop']['location']['id'],
             ]);
    }

    public function test_two_checkins_have_been_created_at_setup() {
        $this->assertEquals(2, $this->user->statuses->count());
    }

    /**
     * Expection: Runs and does not throw errors.
     */

    public function test_pdf_export() {
        $this->actingAs($this->user)
             ->get(route('export.generate'), [
                 'begin'    => date('Y-m-d', time()), // now
                 'end'      => date('Y-m-d', time() + 4 * 24 * 60 * 60), // in four days
                 'filetype' => 'pdf'
             ]);
    }

    public function test_json_export() {
        $this->actingAs($this->user)
             ->get('/export-generate', [
                 'begin'    => date('Y-m-d', time()), // now
                 'end'      => date('Y-m-d', time() + 4 * 24 * 60 * 60), // in four days
                 'filetype' => 'json'
             ]);
    }

    public function test_csv_export() {
        $this->followingRedirects();
        $this->actingAs($this->user)
             ->get(route('export.generate'), [
                 'begin'    => date('Y-m-d', time()), // now
                 'end'      => date('Y-m-d', time() + 4 * 24 * 60 * 60), // in four days
                 'filetype' => 'csv'
             ]);
    }
}
