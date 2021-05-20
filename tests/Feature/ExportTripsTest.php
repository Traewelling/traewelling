<?php

namespace Tests\Feature;

use App\Http\Controllers\TransportController;
use Carbon\Carbon;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTripsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void {
        parent::setUp();

        $this->user = $this->createGDPRAckedUser();

        $this->checkin("Frankfurt(M) Flughafen Fernbf", new DateTime("+1 day 8:00"));
        $this->checkin("Hamburg Hbf", new DateTime("+2 day 7:45"));
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
