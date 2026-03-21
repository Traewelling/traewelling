<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Event\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\FeatureTestCase;

class EventSuggestionCorrectionTest extends FeatureTestCase
{
    use RefreshDatabase, WithFaker;

    public function test_suggestion_hash_tag_correction(): void
    {
        $user = User::factory()->create();

        $suggestion = new EventService()->suggestEvent(
            user: $user,
            name: $this->faker->name,
            begin: now(),
            end: now()->addDay(),
            hashtag: '#GreatTestCase',
        );

        $this->assertEquals('GreatTestCase', $suggestion->hashtag);
    }

    public function test_suggestion_hash_tag_without_correction(): void
    {
        $user = User::factory()->create();

        $suggestion = new EventService()->suggestEvent(
            user: $user,
            name: $this->faker->name,
            begin: now(),
            end: now()->addDay(),
            hashtag: 'GreatTestCase',
        );

        $this->assertEquals('GreatTestCase', $suggestion->hashtag);
    }
}
