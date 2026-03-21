<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\Event;
use App\Models\EventSuggestion;
use App\Repositories\EventRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\FeatureTestCase;

class EventRepositoryTest extends FeatureTestCase
{
    use RefreshDatabase;

    private EventRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EventRepository();
    }

    public function test_paginate_for_admin_buckets_events_by_status(): void
    {
        $future = Event::factory()->create(['checkin_start' => now()->addDays(5)->toDateString(), 'checkin_end' => now()->addDays(10)->toDateString()]);
        $current = Event::factory()->create(['checkin_start' => now()->subDay()->toDateString(), 'checkin_end' => now()->addDay()->toDateString()]);
        $past = Event::factory()->create(['checkin_start' => now()->subDays(10)->toDateString(), 'checkin_end' => now()->subDay()->toDateString()]);

        $result = $this->repository->paginateForAdmin(null);

        $this->assertTrue($result['future']->getCollection()->contains('id', $future->id));
        $this->assertFalse($result['future']->getCollection()->contains('id', $current->id));
        $this->assertFalse($result['future']->getCollection()->contains('id', $past->id));

        $this->assertTrue($result['current']->getCollection()->contains('id', $current->id));
        $this->assertFalse($result['current']->getCollection()->contains('id', $future->id));
        $this->assertFalse($result['current']->getCollection()->contains('id', $past->id));

        $this->assertTrue($result['past']->getCollection()->contains('id', $past->id));
        $this->assertFalse($result['past']->getCollection()->contains('id', $future->id));
        $this->assertFalse($result['past']->getCollection()->contains('id', $current->id));
    }

    public function test_paginate_for_admin_filters_by_search(): void
    {
        $match = Event::factory()->create(['name' => 'Berliner Bahnhofsfest', 'checkin_start' => now()->addDay()->toDateString(), 'checkin_end' => now()->addDays(3)->toDateString()]);
        $noMatch = Event::factory()->create(['name' => 'Hamburger Hafengeburtstag', 'checkin_start' => now()->addDay()->toDateString(), 'checkin_end' => now()->addDays(3)->toDateString()]);

        $result = $this->repository->paginateForAdmin('Berliner');

        $this->assertTrue($result['future']->getCollection()->contains('id', $match->id));
        $this->assertFalse($result['future']->getCollection()->contains('id', $noMatch->id));
    }

    public function test_paginate_for_admin_returns_empty_buckets_when_no_events(): void
    {
        $result = $this->repository->paginateForAdmin(null);

        $this->assertCount(0, $result['future']);
        $this->assertCount(0, $result['current']);
        $this->assertCount(0, $result['past']);
    }

    public function test_paginate_for_admin_ignores_empty_search(): void
    {
        $event = Event::factory()->create(['checkin_start' => now()->addDay()->toDateString(), 'checkin_end' => now()->addDays(3)->toDateString()]);

        $resultNull = $this->repository->paginateForAdmin(null);
        $resultEmpty = $this->repository->paginateForAdmin('');

        $this->assertTrue($resultNull['future']->getCollection()->contains('id', $event->id));
        $this->assertTrue($resultEmpty['future']->getCollection()->contains('id', $event->id));
    }

    public function test_paginate_for_admin_strips_html_tags_from_search(): void
    {
        $match = Event::factory()->create(['name' => 'Testfest', 'checkin_start' => now()->addDay()->toDateString(), 'checkin_end' => now()->addDays(3)->toDateString()]);

        $result = $this->repository->paginateForAdmin('<b>Testfest</b>');

        $this->assertTrue($result['future']->getCollection()->contains('id', $match->id));
    }

    public function test_paginate_for_admin_returns_length_aware_paginators(): void
    {
        $result = $this->repository->paginateForAdmin(null);

        $this->assertArrayHasKey('future', $result);
        $this->assertArrayHasKey('current', $result);
        $this->assertArrayHasKey('past', $result);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result['future']);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result['current']);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result['past']);
    }

    public function test_paginate_for_admin_cursor_returns_all_when_no_status(): void
    {
        $future = Event::factory()->create(['checkin_start' => now()->addDays(5)->toDateString(), 'checkin_end' => now()->addDays(10)->toDateString()]);
        $current = Event::factory()->create(['checkin_start' => now()->subDay()->toDateString(), 'checkin_end' => now()->addDay()->toDateString()]);
        $past = Event::factory()->create(['checkin_start' => now()->subDays(10)->toDateString(), 'checkin_end' => now()->subDay()->toDateString()]);

        $result = $this->repository->paginateForAdminCursor(null, null);

        $ids = $result->pluck('id');
        $this->assertTrue($ids->contains($future->id));
        $this->assertTrue($ids->contains($current->id));
        $this->assertTrue($ids->contains($past->id));
    }

    public function test_paginate_for_admin_cursor_filters_future(): void
    {
        $future = Event::factory()->create(['checkin_start' => now()->addDays(5)->toDateString(), 'checkin_end' => now()->addDays(10)->toDateString()]);
        $past = Event::factory()->create(['checkin_start' => now()->subDays(10)->toDateString(), 'checkin_end' => now()->subDay()->toDateString()]);

        $result = $this->repository->paginateForAdminCursor(null, 'future');

        $ids = $result->pluck('id');
        $this->assertTrue($ids->contains($future->id));
        $this->assertFalse($ids->contains($past->id));
    }

    public function test_paginate_for_admin_cursor_filters_current(): void
    {
        $current = Event::factory()->create(['checkin_start' => now()->subDay()->toDateString(), 'checkin_end' => now()->addDay()->toDateString()]);
        $future = Event::factory()->create(['checkin_start' => now()->addDays(5)->toDateString(), 'checkin_end' => now()->addDays(10)->toDateString()]);

        $result = $this->repository->paginateForAdminCursor(null, 'current');

        $ids = $result->pluck('id');
        $this->assertTrue($ids->contains($current->id));
        $this->assertFalse($ids->contains($future->id));
    }

    public function test_paginate_for_admin_cursor_filters_past(): void
    {
        $past = Event::factory()->create(['checkin_start' => now()->subDays(10)->toDateString(), 'checkin_end' => now()->subDay()->toDateString()]);
        $future = Event::factory()->create(['checkin_start' => now()->addDays(5)->toDateString(), 'checkin_end' => now()->addDays(10)->toDateString()]);

        $result = $this->repository->paginateForAdminCursor(null, 'past');

        $ids = $result->pluck('id');
        $this->assertTrue($ids->contains($past->id));
        $this->assertFalse($ids->contains($future->id));
    }

    public function test_paginate_for_admin_cursor_filters_by_search(): void
    {
        $match = Event::factory()->create(['name' => 'Berliner Bahnhofsfest', 'checkin_start' => now()->addDay()->toDateString(), 'checkin_end' => now()->addDays(3)->toDateString()]);
        $noMatch = Event::factory()->create(['name' => 'Hamburger Hafengeburtstag', 'checkin_start' => now()->addDay()->toDateString(), 'checkin_end' => now()->addDays(3)->toDateString()]);

        $result = $this->repository->paginateForAdminCursor('Berliner', null);

        $ids = $result->pluck('id');
        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($noMatch->id));
    }

    public function test_paginate_open_suggestions_returns_only_unprocessed_future(): void
    {
        $open = EventSuggestion::factory()->create(['processed' => false, 'end' => now()->addDays(5)->toDateString()]);
        $closed = EventSuggestion::factory()->create(['processed' => true, 'end' => now()->addDays(5)->toDateString()]);
        $past = EventSuggestion::factory()->create(['processed' => false, 'end' => now()->subDay()->toDateString()]);

        $result = $this->repository->paginateOpenSuggestions();

        $ids = $result->pluck('id');
        $this->assertTrue($ids->contains($open->id));
        $this->assertFalse($ids->contains($closed->id));
        $this->assertFalse($ids->contains($past->id));
    }

    public function test_find_parallel_events_returns_overlapping_events(): void
    {
        $suggestion = EventSuggestion::factory()->create([
            'begin' => now()->addDay()->toDateString(),
            'end' => now()->addDays(5)->toDateString(),
        ]);

        // Fully contained within suggestion range
        $inside = Event::factory()->create([
            'checkin_start' => now()->addDays(2)->toDateString(),
            'checkin_end' => now()->addDays(4)->toDateString(),
        ]);

        // Overlaps at the start
        $overlapStart = Event::factory()->create([
            'checkin_start' => now()->subDay()->toDateString(),
            'checkin_end' => now()->addDays(2)->toDateString(),
        ]);

        // Overlaps at the end
        $overlapEnd = Event::factory()->create([
            'checkin_start' => now()->addDays(4)->toDateString(),
            'checkin_end' => now()->addDays(7)->toDateString(),
        ]);

        // No overlap
        $noOverlap = Event::factory()->create([
            'checkin_start' => now()->addDays(10)->toDateString(),
            'checkin_end' => now()->addDays(15)->toDateString(),
        ]);

        $result = $this->repository->findParallelEventsWithSimilarity($suggestion);

        $ids = $result->pluck('id');
        $this->assertTrue($ids->contains($inside->id));
        $this->assertTrue($ids->contains($overlapStart->id));
        $this->assertTrue($ids->contains($overlapEnd->id));
        $this->assertFalse($ids->contains($noOverlap->id));
    }

    public function test_find_parallel_events_sorts_by_name_similarity(): void
    {
        $suggestion = EventSuggestion::factory()->create([
            'name' => 'Berliner Bahnhofsfest',
            'begin' => now()->addDay()->toDateString(),
            'end' => now()->addDays(5)->toDateString(),
        ]);

        $similar = Event::factory()->create([
            'name' => 'Berliner Bahnhofsfest 2025',
            'checkin_start' => now()->addDays(2)->toDateString(),
            'checkin_end' => now()->addDays(4)->toDateString(),
        ]);

        $dissimilar = Event::factory()->create([
            'name' => 'Hamburger Hafengeburtstag',
            'checkin_start' => now()->addDays(2)->toDateString(),
            'checkin_end' => now()->addDays(4)->toDateString(),
        ]);

        $result = $this->repository->findParallelEventsWithSimilarity($suggestion);

        $this->assertEquals($similar->id, $result->first()->id);
        $this->assertGreaterThan($result->last()->similarity, $result->first()->similarity);
    }
}
