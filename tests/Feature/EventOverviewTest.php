<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class EventOverviewTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_overview_page_is_accessible(): void
    {
        $this->get(route('events'))->assertOk();
    }

    public function test_current_event_is_shown(): void
    {
        $event = Event::factory()->create([
            'checkin_start' => now()->subDay()->toDateString(),
            'checkin_end' => now()->addDay()->toDateString(),
        ]);

        $this->get(route('events'))->assertSee($event->name);
    }

    public function test_upcoming_event_is_shown(): void
    {
        $event = Event::factory()->create([
            'checkin_start' => now()->addDays(5)->toDateString(),
            'checkin_end' => now()->addDays(10)->toDateString(),
        ]);

        $this->get(route('events'))->assertSee($event->name);
    }

    public function test_past_event_is_not_shown(): void
    {
        $event = Event::factory()->create([
            'checkin_start' => now()->subDays(10)->toDateString(),
            'checkin_end' => now()->subDay()->toDateString(),
        ]);

        $this->get(route('events'))->assertDontSee($event->name);
    }
}
