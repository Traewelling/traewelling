<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Enum\Business;
use App\Models\Checkin;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Tests\ApiTestCase;

class TicketTest extends ApiTestCase
{
    use RefreshDatabase;

    private function createClosedBetaUser(): User
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('closed-beta');
        $user->assignRole($role);

        return $user;
    }

    public function test_user_without_closed_beta_role_cannot_list_tickets(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $this->get('/api/v1/tickets')->assertStatus(403);
    }

    public function test_user_without_closed_beta_role_cannot_create_ticket(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $this->post('/api/v1/tickets', ['name' => 'Test'])->assertStatus(403);
    }

    public function test_closed_beta_user_can_list_own_tickets(): void
    {
        $user = $this->createClosedBetaUser();
        Ticket::factory(['user_id' => $user->id, 'name' => 'My Ticket'])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'My Ticket');
    }

    public function test_valid_on_filter_returns_matching_tickets(): void
    {
        $user = $this->createClosedBetaUser();
        Ticket::factory(['user_id' => $user->id, 'name' => 'Valid', 'valid_from' => '2026-01-01', 'valid_until' => '2026-12-31'])->create();
        Ticket::factory(['user_id' => $user->id, 'name' => 'Expired', 'valid_from' => '2025-01-01', 'valid_until' => '2025-12-31'])->create();
        Ticket::factory(['user_id' => $user->id, 'name' => 'No dates', 'valid_from' => null, 'valid_until' => null])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets?validOn=2026-06-15');
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Valid'));
        $this->assertTrue($names->contains('No dates'));
        $this->assertFalse($names->contains('Expired'));
    }

    public function test_closed_beta_user_cannot_see_other_users_tickets(): void
    {
        $user = $this->createClosedBetaUser();
        $otherUser = User::factory()->create();
        Ticket::factory(['user_id' => $otherUser->id])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_closed_beta_user_can_create_ticket(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $response = $this->post('/api/v1/tickets', [
            'name' => 'Deutschlandticket',
            'valid_from' => '2026-03-01',
            'valid_until' => '2026-03-31',
            'price' => 49.00,
            'currency' => 'EUR',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Deutschlandticket');
        $response->assertJsonPath('data.validFrom', '2026-03-01');
        $response->assertJsonPath('data.validUntil', '2026-03-31');
        $response->assertJsonPath('data.price', 49);
        $response->assertJsonPath('data.currency', 'EUR');

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'name' => 'Deutschlandticket',
            'currency' => 'EUR',
        ]);
    }

    public function test_create_ticket_without_optional_fields(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $response = $this->post('/api/v1/tickets', ['name' => 'Simple Ticket']);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Simple Ticket');
        $response->assertJsonPath('data.validFrom', null);
        $response->assertJsonPath('data.validUntil', null);
        $response->assertJsonPath('data.price', null);
        $response->assertJsonPath('data.currency', null);
    }

    public function test_create_ticket_requires_name(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $this->post('/api/v1/tickets', [])->assertStatus(422);
    }

    public function test_create_ticket_rejects_invalid_date_range(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $this->post('/api/v1/tickets', [
            'name' => 'Bad Dates',
            'valid_from' => '2026-03-31',
            'valid_until' => '2026-03-01',
        ])->assertStatus(422);
    }

    public function test_closed_beta_user_can_show_own_ticket(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id);
        $response->assertOk();
        $response->assertJsonPath('data.id', $ticket->id);
    }

    public function test_closed_beta_user_cannot_show_other_users_ticket(): void
    {
        $user = $this->createClosedBetaUser();
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory(['user_id' => $otherUser->id])->create();
        Passport::actingAs($user, ['*']);

        $this->get('/api/v1/tickets/' . $ticket->id)->assertStatus(404);
    }

    public function test_closed_beta_user_can_update_own_ticket(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id, 'name' => 'Old Name'])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->put('/api/v1/tickets/' . $ticket->id, ['name' => 'New Name']);
        $response->assertOk();
        $response->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'name' => 'New Name']);
    }

    public function test_closed_beta_user_cannot_update_other_users_ticket(): void
    {
        $user = $this->createClosedBetaUser();
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory(['user_id' => $otherUser->id])->create();
        Passport::actingAs($user, ['*']);

        $this->put('/api/v1/tickets/' . $ticket->id, ['name' => 'Hacked'])->assertStatus(404);
    }

    public function test_closed_beta_user_can_delete_own_ticket(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        Passport::actingAs($user, ['*']);

        $this->delete('/api/v1/tickets/' . $ticket->id)->assertStatus(204);
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_closed_beta_user_cannot_delete_other_users_ticket(): void
    {
        $user = $this->createClosedBetaUser();
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory(['user_id' => $otherUser->id])->create();
        Passport::actingAs($user, ['*']);

        $this->delete('/api/v1/tickets/' . $ticket->id)->assertStatus(404);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
    }

    public function test_delete_ticket_sets_status_ticket_id_to_null(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        $status = Status::factory(['user_id' => $user->id, 'ticket_id' => $ticket->id])->create();
        Passport::actingAs($user, ['*']);

        $this->delete('/api/v1/tickets/' . $ticket->id)->assertStatus(204);
        $this->assertDatabaseHas('statuses', ['id' => $status->id, 'ticket_id' => null]);
    }

    public function test_can_assign_own_ticket_to_status(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        $status = $checkin->status;
        Passport::actingAs($user, ['*']);

        $response = $this->put('/api/v1/statuses/' . $status->id . '/tickets', [
            'ticketId' => $ticket->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('statuses', ['id' => $status->id, 'ticket_id' => $ticket->id]);
    }

    public function test_cannot_assign_other_users_ticket_to_own_status(): void
    {
        $user = $this->createClosedBetaUser();
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory(['user_id' => $otherUser->id])->create();
        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        $status = $checkin->status;
        Passport::actingAs($user, ['*']);

        $response = $this->put('/api/v1/statuses/' . $status->id . '/tickets', [
            'ticketId' => $ticket->id,
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('statuses', ['id' => $status->id, 'ticket_id' => null]);
    }

    public function test_can_remove_ticket_from_status(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        $status = $checkin->status;
        $status->update(['ticket_id' => $ticket->id]);
        Passport::actingAs($user, ['*']);

        $response = $this->put('/api/v1/statuses/' . $status->id . '/tickets', [
            'ticketId' => null,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('statuses', ['id' => $status->id, 'ticket_id' => null]);
    }

    public function test_cannot_assign_ticket_to_other_users_status(): void
    {
        $user = $this->createClosedBetaUser();
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        $checkin = Checkin::factory(['user_id' => $otherUser->id])->create();
        $status = $checkin->status;
        Passport::actingAs($user, ['*']);

        $response = $this->put('/api/v1/statuses/' . $status->id . '/tickets', [
            'ticketId' => $ticket->id,
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('statuses', ['id' => $status->id, 'ticket_id' => $ticket->id]);
    }

    public function test_ticket_appears_in_status_resource_for_owner(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id, 'name' => 'My Pass'])->create();
        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        $status = $checkin->status;
        $status->update(['ticket_id' => $ticket->id]);
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/status/' . $status->id);
        $response->assertOk();
        $response->assertJsonPath('data.ticket.name', 'My Pass');
    }

    public function test_ticket_not_visible_in_status_resource_for_other_users(): void
    {
        $owner = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $owner->id])->create();
        $checkin = Checkin::factory(['user_id' => $owner->id])->create();
        $status = $checkin->status;
        $status->update(['ticket_id' => $ticket->id]);

        $viewer = User::factory()->create();
        Passport::actingAs($viewer, ['*']);

        $response = $this->get('/api/v1/status/' . $status->id);
        $response->assertOk();
        $response->assertJsonPath('data.ticket', null);
    }

    public function test_statistics_returns_correct_counts(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id, 'price' => 100.00, 'currency' => 'EUR'])->create();

        $checkin1 = Checkin::factory(['user_id' => $user->id, 'distance' => 10000, 'duration' => 60])->create();
        $checkin2 = Checkin::factory(['user_id' => $user->id, 'distance' => 20000, 'duration' => 120])->create();
        $checkin1->status->update(['ticket_id' => $ticket->id]);
        $checkin2->status->update(['ticket_id' => $ticket->id]);

        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();
        $response->assertJsonPath('data.tripCount', 2);
        $response->assertJsonPath('data.distance', 30000);
        $response->assertJsonPath('data.duration', 180);
        $response->assertJsonPath('data.costPerTrip', 50);
    }

    public function test_statistics_cost_fields_null_without_price(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id, 'price' => null])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();
        $response->assertJsonPath('data.costPerTrip', null);
        $response->assertJsonPath('data.costPerKm', null);
        $response->assertJsonPath('data.costPerHour', null);
    }

    public function test_statistics_not_accessible_for_other_users(): void
    {
        $owner = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $owner->id])->create();

        $other = User::factory()->create();
        Passport::actingAs($other, ['*']);

        $this->get('/api/v1/tickets/' . $ticket->id . '/statistics')->assertStatus(404);
    }

    public function test_ticket_resource_includes_trip_count(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        $checkin->status->update(['ticket_id' => $ticket->id]);
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets');
        $response->assertOk();
        $response->assertJsonPath('data.0.tripCount', 1);
        $response->assertJsonPath('data.0.totalDistance', $checkin->distance);
    }

    public function test_unauthenticated_cannot_list_tickets(): void
    {
        $this->get('/api/v1/tickets')->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_create_ticket(): void
    {
        $this->post('/api/v1/tickets', ['name' => 'Test'])->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_show_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->get('/api/v1/tickets/' . $ticket->id)->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_update_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->put('/api/v1/tickets/' . $ticket->id, ['name' => 'X'])->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_delete_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->delete('/api/v1/tickets/' . $ticket->id)->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_view_statistics(): void
    {
        $ticket = Ticket::factory()->create();

        $this->get('/api/v1/tickets/' . $ticket->id . '/statistics')->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_assign_ticket_to_status(): void
    {
        $checkin = Checkin::factory()->create();

        $this->put('/api/v1/statuses/' . $checkin->status_id . '/tickets', ['ticketId' => null])
            ->assertUnauthorized();
    }

    public function test_show_nonexistent_ticket_returns_404(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $this->get('/api/v1/tickets/00000000-0000-0000-0000-000000000000')->assertNotFound();
    }

    public function test_update_nonexistent_ticket_returns_404(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $this->put('/api/v1/tickets/00000000-0000-0000-0000-000000000000', ['name' => 'X'])->assertNotFound();
    }

    public function test_delete_nonexistent_ticket_returns_404(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $this->delete('/api/v1/tickets/00000000-0000-0000-0000-000000000000')->assertNotFound();
    }

    public function test_statistics_for_nonexistent_ticket_returns_404(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $this->get('/api/v1/tickets/00000000-0000-0000-0000-000000000000/statistics')->assertNotFound();
    }

    public function test_assign_ticket_to_nonexistent_status_returns_404(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $this->put('/api/v1/statuses/999999999/tickets', ['ticketId' => null])->assertNotFound();
    }

    public function test_update_ticket_rejects_invalid_date_range(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        Passport::actingAs($user, ['*']);

        $this->put('/api/v1/tickets/' . $ticket->id, [
            'valid_from' => '2026-12-31',
            'valid_until' => '2026-01-01',
        ])->assertStatus(422);
    }

    public function test_update_ticket_rejects_negative_price(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        Passport::actingAs($user, ['*']);

        $this->put('/api/v1/tickets/' . $ticket->id, ['price' => -1])->assertStatus(422);
    }

    public function test_assign_ticket_requires_ticket_id_field(): void
    {
        $user = $this->createClosedBetaUser();
        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        Passport::actingAs($user, ['*']);

        // 'ticketId' field must be present (even if null); omitting it is a validation error
        $this->put('/api/v1/statuses/' . $checkin->status_id . '/tickets', [])
            ->assertStatus(422);
    }

    public function test_update_ticket_can_clear_price_to_null(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id, 'price' => 99.0, 'currency' => 'EUR'])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->put('/api/v1/tickets/' . $ticket->id, ['price' => null]);
        $response->assertOk();
        $response->assertJsonPath('data.price', null);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'price' => null]);
    }

    public function test_update_ticket_partial_update_leaves_other_fields_unchanged(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory([
            'user_id' => $user->id,
            'name' => 'Original',
            'price' => 49.0,
            'currency' => 'EUR',
        ])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->put('/api/v1/tickets/' . $ticket->id, ['name' => 'Updated']);
        $response->assertOk();
        $response->assertJsonPath('data.name', 'Updated');
        $this->assertEquals(49.0, $response->json('data.price'));
        $response->assertJsonPath('data.currency', 'EUR');
    }

    public function test_created_ticket_has_zero_aggregates(): void
    {
        $user = $this->createClosedBetaUser();
        Passport::actingAs($user, ['*']);

        $response = $this->post('/api/v1/tickets', ['name' => 'Fresh Ticket']);
        $response->assertStatus(201);
        $response->assertJsonPath('data.tripCount', 0);
        $response->assertJsonPath('data.totalDistance', 0);
        $response->assertJsonPath('data.totalDuration', 0);
    }

    public function test_show_ticket_includes_aggregates(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();
        $checkin = Checkin::factory(['user_id' => $user->id, 'distance' => 5000, 'duration' => 30])->create();
        $checkin->status->update(['ticket_id' => $ticket->id]);
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id);
        $response->assertOk();
        $response->assertJsonPath('data.tripCount', 1);
        $response->assertJsonPath('data.totalDistance', 5000);
        $response->assertJsonPath('data.totalDuration', 30);
    }

    public function test_statistics_for_ticket_with_no_trips_returns_zeros(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id, 'price' => 100.0])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();
        $response->assertJsonPath('data.tripCount', 0);
        $response->assertJsonPath('data.distance', 0);
        $response->assertJsonPath('data.duration', 0);
        $response->assertJsonPath('data.costPerTrip', null);
        $response->assertJsonPath('data.costPerKm', null);
        $response->assertJsonPath('data.costPerHour', null);
        $response->assertJsonPath('data.firstUsed', null);
        $response->assertJsonPath('data.lastUsed', null);
        $response->assertJsonIsArray('data.purposes');
        $response->assertJsonIsArray('data.categories');
        $response->assertJsonIsArray('data.operators');
    }

    public function test_statistics_calculates_cost_per_km_and_per_hour(): void
    {
        $user = $this->createClosedBetaUser();
        // price=100, distance=40km → costPerKm = 100/40 = 2.5 EUR/km
        // duration=70min = 70/60h → costPerHour = 100/(70/60) ≈ 85.71 EUR/h
        $ticket = Ticket::factory(['user_id' => $user->id, 'price' => 100.0, 'currency' => 'EUR'])->create();

        $checkin1 = Checkin::factory(['user_id' => $user->id, 'distance' => 25000, 'duration' => 40])->create();
        $checkin2 = Checkin::factory(['user_id' => $user->id, 'distance' => 15000, 'duration' => 30])->create();
        $checkin1->status->update(['ticket_id' => $ticket->id]);
        $checkin2->status->update(['ticket_id' => $ticket->id]);

        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();
        // 40km total → 100/40 = 2.5 EUR/km
        $response->assertJsonPath('data.costPerKm', 2.5);
        // 70min = 70/60h → round(100/(70/60), 2) = round(85.714..., 2) = 85.71 EUR/h
        $response->assertJsonPath('data.costPerHour', 85.71);
    }

    public function test_statistics_cost_per_km_null_when_distance_is_zero(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id, 'price' => 100.0])->create();
        $checkin = Checkin::factory(['user_id' => $user->id, 'distance' => 0, 'duration' => 60])->create();
        $checkin->status->update(['ticket_id' => $ticket->id]);
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();
        $response->assertJsonPath('data.costPerKm', null);
        // 60min = 1h → 100/1 = 100 EUR/h
        $this->assertEquals(100.0, $response->json('data.costPerHour'));
    }

    public function test_statistics_cost_per_hour_null_when_duration_is_zero(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id, 'price' => 100.0])->create();
        $checkin = Checkin::factory(['user_id' => $user->id, 'distance' => 10000, 'duration' => 0])->create();
        $checkin->status->update(['ticket_id' => $ticket->id]);
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();
        $response->assertJsonPath('data.costPerHour', null);
        // 10km → 100/10 = 10 EUR/km
        $this->assertEquals(10.0, $response->json('data.costPerKm'));
    }

    public function test_statistics_first_and_last_used_populated(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();

        $checkin1 = Checkin::factory(['user_id' => $user->id])->create();
        $checkin2 = Checkin::factory(['user_id' => $user->id])->create();
        // Force known departure dates (UTCDateTime requires timezone-aware format)
        $checkin1->update(['departure' => '2026-01-10T08:00:00Z']);
        $checkin2->update(['departure' => '2026-03-05T12:00:00Z']);
        $checkin1->status->update(['ticket_id' => $ticket->id]);
        $checkin2->status->update(['ticket_id' => $ticket->id]);

        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();
        $response->assertJsonPath('data.firstUsed', '2026-01-10');
        $response->assertJsonPath('data.lastUsed', '2026-03-05');
    }

    public function test_statistics_purposes_breakdown_groups_by_business(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();

        $private = Checkin::factory(['user_id' => $user->id, 'distance' => 1000])->create();
        $commute = Checkin::factory(['user_id' => $user->id, 'distance' => 2000])->create();
        $private->status->update(['ticket_id' => $ticket->id, 'business' => Business::PRIVATE]);
        $commute->status->update(['ticket_id' => $ticket->id, 'business' => Business::COMMUTE]);

        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();

        $purposes = collect($response->json('data.purposes'));
        $this->assertCount(2, $purposes);

        $privateEntry = $purposes->firstWhere('reason', (string) Business::PRIVATE->value);
        $this->assertNotNull($privateEntry);
        $this->assertEquals(1, $privateEntry['count']);
        $this->assertEquals(1000, $privateEntry['distance']);

        $commuteEntry = $purposes->firstWhere('reason', (string) Business::COMMUTE->value);
        $this->assertNotNull($commuteEntry);
        $this->assertEquals(1, $commuteEntry['count']);
        $this->assertEquals(2000, $commuteEntry['distance']);
    }

    public function test_statistics_categories_breakdown_groups_by_transport_type(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();

        $checkin1 = Checkin::factory(['user_id' => $user->id])->create();
        $checkin2 = Checkin::factory(['user_id' => $user->id])->create();
        // Force known categories on the underlying trips
        $checkin1->trip->update(['category' => 'tram']);
        $checkin2->trip->update(['category' => 'tram']);
        $checkin1->status->update(['ticket_id' => $ticket->id]);
        $checkin2->status->update(['ticket_id' => $ticket->id]);

        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();

        $categories = collect($response->json('data.categories'));
        $tramEntry = $categories->firstWhere('name', 'tram');
        $this->assertNotNull($tramEntry);
        $this->assertEquals(2, $tramEntry['count']);
    }

    public function test_statistics_operators_breakdown_lists_top_operators(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id])->create();

        $checkin = Checkin::factory(['user_id' => $user->id, 'distance' => 50000])->create();
        $checkin->status->update(['ticket_id' => $ticket->id]);
        $operatorName = $checkin->trip->operator->name;

        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tickets/' . $ticket->id . '/statistics');
        $response->assertOk();

        $operators = collect($response->json('data.operators'));
        $this->assertNotEmpty($operators);
        $operatorEntry = $operators->firstWhere('name', $operatorName);
        $this->assertNotNull($operatorEntry);
        $this->assertEquals(50000, $operatorEntry['distance']);
    }

    public function test_assign_ticket_response_contains_status_resource_with_ticket(): void
    {
        $user = $this->createClosedBetaUser();
        $ticket = Ticket::factory(['user_id' => $user->id, 'name' => 'BahnCard'])->create();
        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->put('/api/v1/statuses/' . $checkin->status_id . '/tickets', [
            'ticketId' => $ticket->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.ticket.name', 'BahnCard');
        $response->assertJsonPath('data.id', $checkin->status_id);
    }
}
