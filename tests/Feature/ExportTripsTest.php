<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\StatusController as StatusBackend;

use Barryvdh\DomPDF\Facade as PDF;

class ExportTripsTest extends TestCase {
    use RefreshDatabase;

    private $user;

    protected function setUp(): void {
        parent::setUp();

        $this->user = factory(User::class)->create();
            $response = $this->actingAs($this->user)
                            ->post('/gdpr-ack');
            $response->assertStatus(302);
            $response->assertRedirect('/');
        
        $this->checkin("Frankfurt(M) Flughafen Fernbf", "8070003", new \DateTime("+1 day 8:00"));
        $this->checkin("Essen Hbf", "8000098", new \DateTime("+2 day 8:00"));
    }

    /**
     * This is mostly copied from Checkin Test
     */
    protected function checkin($stationname, $ibnr, \DateTime $now) {
        $trainStationboard = TransportController::TrainStationboard($stationname, $now->format('U'), 'express');

        $countDepartures = count($trainStationboard['departures']);
        if($countDepartures == 0) {
            $this->markTestSkipped("Unable to find matching trains. Is it night in $stationname?");
            return;
        }
        
        // Second: We don't like broken or cancelled trains.
        $i = 0;
        while (
              (isset($trainStationboard['departures'][$i]->cancelled)
                && $trainStationboard['departures'][$i]->cancelled
              )
              || count($trainStationboard['departures'][$i]->remarks) != 0
            ) {
            $i++;
            if ($i == $countDepartures) {
                $this->markSkippedForMissingDependecy("Unable to find unbroken train. Is it stormy in $stationname?");
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
        $response = $this->actingAs($this->user)
            ->post(route('trains.checkin'), [
                'body' => 'Example Body',
                'tripID' => $departure->tripId,
                'start' => $ibnr,
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
        $pdfResponse = $this->actingAs($this->user)
             ->get(route('export.generate'), [
                 'begin' => date('Y-m-d', time()), // now
                 'end' => date('Y-m-d', time() + 4*24*60*60), // in four days
                 'filetype' => 'pdf'
             ]);
    }

    public function test_json_export() {
        $jsonResponse = $this->actingAs($this->user)
             ->get('/export-generate', [
                 'begin' => date('Y-m-d', time()), // now
                 'end' => date('Y-m-d', time() + 4*24*60*60), // in four days
                 'filetype' => 'json'
             ]);
    }

    public function test_csv_export() {
        $this->followingRedirects();
        $csvResponse = $this->actingAs($this->user)
             ->get(route('export.generate'), [
                 'begin' => date('Y-m-d', time()), // now
                 'end' => date('Y-m-d', time() + 4*24*60*60), // in four days
                 'filetype' => 'csv'
             ]);
    }
}