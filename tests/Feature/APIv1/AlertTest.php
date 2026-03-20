<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class AlertTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->user = User::factory()->create();
    }

    private function alertPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'info',
            'active_from' => now()->subDay()->toDateString(),
            'active_until' => now()->addWeek()->toDateString(),
            'title_de' => 'Testtitel',
            'content_de' => 'Testinhalt',
            'title_en' => 'Test title',
            'content_en' => 'Test content',
            'url_de' => null,
            'url_en' => null,
            'url' => null,
        ], $overrides);
    }

    public function test_public_index_returns_only_active_alerts(): void
    {
        $active = Alert::factory()->create(['active_from' => now()->subDay(), 'active_until' => now()->addWeek()]);
        $active->translations()->createMany([
            ['locale' => 'de', 'title' => 'Aktiv', 'content' => 'Inhalt'],
            ['locale' => 'en', 'title' => 'Active', 'content' => 'Content'],
        ]);

        $inactive = Alert::factory()->inactive()->create();
        $inactive->translations()->createMany([
            ['locale' => 'de', 'title' => 'Inaktiv', 'content' => 'Inhalt'],
            ['locale' => 'en', 'title' => 'Inactive', 'content' => 'Content'],
        ]);

        $this->actAsApiUserWithAllScopes($this->user);
        $res = $this->getJson('/api/v1/alerts');

        $res->assertOk();
        $ids = collect($res->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($active->id));
        $this->assertFalse($ids->contains($inactive->id));
    }

    public function test_public_index_does_not_return_all_for_non_admin(): void
    {
        Alert::factory()->inactive()->create();

        $this->actAsApiUserWithAllScopes($this->user);
        $res = $this->getJson('/api/v1/alerts?all=true');

        $res->assertOk();
        $this->assertEmpty($res->json('data'));
    }

    public function test_admin_index_returns_all_alerts(): void
    {
        $active = Alert::factory()->create();
        $inactive = Alert::factory()->inactive()->create();
        foreach ([$active, $inactive] as $alert) {
            $alert->translations()->createMany([
                ['locale' => 'de', 'title' => 'T', 'content' => 'C'],
                ['locale' => 'en', 'title' => 'T', 'content' => 'C'],
            ]);
        }

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/alerts?all=true');

        $res->assertOk();
        $ids = collect($res->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($active->id));
        $this->assertTrue($ids->contains($inactive->id));
    }

    public function test_admin_can_get_single_alert(): void
    {
        $alert = Alert::factory()->create();
        $alert->translations()->createMany([
            ['locale' => 'de', 'title' => 'Titel', 'content' => 'Inhalt'],
            ['locale' => 'en', 'title' => 'Title', 'content' => 'Content'],
        ]);

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson("/api/v1/alerts/{$alert->id}");

        $res->assertOk();
        $res->assertJsonPath('data.id', $alert->id);
        $res->assertJsonStructure(['data' => ['id', 'type', 'active_from', 'active_until', 'url', 'translations']]);
    }

    public function test_non_admin_cannot_get_single_alert(): void
    {
        $alert = Alert::factory()->create();

        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson("/api/v1/alerts/{$alert->id}")->assertForbidden();
    }

    public function test_get_single_alert_returns_404_for_unknown_id(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->getJson('/api/v1/alerts/00000000-0000-0000-0000-000000000000')->assertNotFound();
    }

    public function test_admin_can_create_alert(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->postJson('/api/v1/alerts', $this->alertPayload());

        $res->assertCreated();
        $res->assertJsonPath('data.type', 'info');
        $this->assertDatabaseHas('alerts', ['type' => 'info']);
        $this->assertDatabaseHas('alert_translations', ['locale' => 'de', 'title' => 'Testtitel']);
        $this->assertDatabaseHas('alert_translations', ['locale' => 'en', 'title' => 'Test title']);
    }

    public function test_non_admin_cannot_create_alert(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->postJson('/api/v1/alerts', $this->alertPayload())->assertForbidden();
    }

    public function test_create_alert_validates_required_fields(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->postJson('/api/v1/alerts', [])->assertUnprocessable();
    }

    public function test_create_alert_validates_active_until_after_active_from(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->postJson('/api/v1/alerts', $this->alertPayload([
            'active_from' => now()->toDateString(),
            'active_until' => now()->subDay()->toDateString(),
        ]));
        $res->assertUnprocessable();
    }

    public function test_admin_can_update_alert(): void
    {
        $alert = Alert::factory()->create(['type' => 'info']);
        $alert->translations()->createMany([
            ['locale' => 'de', 'title' => 'Alt', 'content' => 'Inhalt'],
            ['locale' => 'en', 'title' => 'Old', 'content' => 'Content'],
        ]);

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->putJson("/api/v1/alerts/{$alert->id}", $this->alertPayload(['type' => 'warning', 'title_de' => 'Neu', 'title_en' => 'New']));

        $res->assertOk();
        $res->assertJsonPath('data.type', 'warning');
        $this->assertDatabaseHas('alerts', ['id' => $alert->id, 'type' => 'warning']);
        $this->assertDatabaseHas('alert_translations', ['locale' => 'de', 'title' => 'Neu']);
        $this->assertDatabaseHas('alert_translations', ['locale' => 'en', 'title' => 'New']);
    }

    public function test_non_admin_cannot_update_alert(): void
    {
        $alert = Alert::factory()->create();

        $this->actAsApiUserWithAllScopes($this->user);
        $this->putJson("/api/v1/alerts/{$alert->id}", $this->alertPayload())->assertForbidden();
    }

    public function test_update_alert_returns_404_for_unknown_id(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->putJson('/api/v1/alerts/00000000-0000-0000-0000-000000000000', $this->alertPayload())->assertNotFound();
    }

    public function test_admin_can_delete_alert(): void
    {
        $alert = Alert::factory()->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson("/api/v1/alerts/{$alert->id}")->assertNoContent();

        $this->assertDatabaseMissing('alerts', ['id' => $alert->id]);
    }

    public function test_non_admin_cannot_delete_alert(): void
    {
        $alert = Alert::factory()->create();

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson("/api/v1/alerts/{$alert->id}")->assertForbidden();
    }

    public function test_delete_alert_returns_404_for_unknown_id(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson('/api/v1/alerts/00000000-0000-0000-0000-000000000000')->assertNotFound();
    }

    public function test_unauthenticated_cannot_create_alert(): void
    {
        $this->postJson('/api/v1/alerts', $this->alertPayload())->assertUnauthorized();
    }

    public function test_year_in_review_alert_is_injected_when_enabled(): void
    {
        config(['trwl.year_in_review.alert' => true]);

        $this->actAsApiUserWithAllScopes($this->user);
        $res = $this->getJson('/api/v1/alerts');

        $res->assertOk();
        $ids = collect($res->json('data'))->pluck('id');
        $this->assertTrue($ids->contains('year-in-review-' . date('Y')));
    }
}
