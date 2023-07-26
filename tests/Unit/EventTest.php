<?php

namespace Tests\Unit;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function testIsPrideAttribute(): void {
    	$positiveCases = [
    		'Cologne Pride',
    		'22. Gulaschpridenacht',
    		'CSD Hamburg',
    		'csd-munich',
    	];
    	foreach ($positiveCases as $case) {
	        $this->assertTrue($this->makeEventWithName($case)->isPride);
	    }

	    $negativeCases = [
    		'37c3',
    		'Evangelischer Kirchentag Nürnberg',
    		'DAS FEST Karlsruhe',
    	];
    	foreach ($negativeCases as $case) {
	        $this->assertFalse($this->makeEventWithName($case)->isPride);
	    }
    }

    private function makeEventWithName(string $name) {
        return Event::factory()->create(['name' => $name]);
    }
}
