<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\EventController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EventSuggestionCorrectionTest extends TestCase {
    use RefreshDatabase;
    use WithFaker;

    public function testSuggestionHashTagCorrection() {
        $user = User::factory()->create();

        $suggestion = EventController::suggestEvent(
            user: $user,
            name: $this->faker->name,
            begin: Carbon::now(),
            end: Carbon::now()->addDay(),
            hashtag: '#GreatTestCase',
        );

        $this->assertEquals('GreatTestCase', $suggestion->hashtag);
    }
}
