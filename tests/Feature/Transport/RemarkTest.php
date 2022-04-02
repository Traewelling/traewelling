<?php

namespace Tests\Feature\Transport;

use App\Models\HafasTrip;
use App\Models\Remark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemarkTest extends TestCase
{

    use RefreshDatabase;

    public function testRemarkRelationship(): void {
        $this->assertDatabaseCount('hafas_trips', 0);
        $this->assertDatabaseCount('remarks', 0);

        $hafasTrip = HafasTrip::factory()->create();
        $remark    = Remark::factory()->create();
        $hafasTrip->remarks()->syncWithoutDetaching([$remark->id]);
        $hafasTrip->refresh();

        $this->assertDatabaseCount('hafas_trips', 1);
        $this->assertDatabaseCount('remarks', 1);

        $this->assertCount(1, $hafasTrip->remarks);
        $this->assertEquals($remark->id, $hafasTrip->remarks->first()->id);

        $this->assertDatabaseHas('trip_remarks', [
            'trip_id'   => $hafasTrip->id,
            'remark_id' => $remark->id,
        ]);
    }
}
