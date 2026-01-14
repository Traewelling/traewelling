<?php

namespace Tests\Feature\Privacy\Status;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\User\FollowController as FollowBackend;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Status;
use App\Models\TrustedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class ViewTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_view_public_status(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::PUBLIC->value])
            ->has(Checkin::factory())
            ->create();

        $this->assertGuest();
        $statusRequest = $this->getJson('/api/v1/status/' . $status->id);
        $statusRequest->assertOk();
    }

    public function test_unauthenticated_view_private_status(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::PRIVATE])
            ->has(Checkin::factory())
            ->create();

        $this->assertGuest();
        $statusRequest = $this->getJson('/api/v1/status/' . $status->id);
        $statusRequest->assertForbidden();
    }

    public function test_unauthenticated_view_unlisted_status(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::UNLISTED])
            ->has(Checkin::factory())
            ->create();

        $this->assertGuest();
        $statusRequest = $this->getJson('/api/v1/status/' . $status->id);
        $statusRequest->assertOk();
    }

    public function test_unauthenticated_view_followers_only_status(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::FOLLOWERS])
            ->has(Checkin::factory())
            ->create();

        $this->assertGuest();
        $statusRequest = $this->getJson('/api/v1/status/' . $status->id);
        $statusRequest->assertForbidden();
    }

    public function test_unauthenticated_view_only_authenticated_users_status(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::AUTHENTICATED])
            ->has(Checkin::factory())
            ->create();

        $this->assertGuest();
        $statusRequest = $this->getJson('/api/v1/status/' . $status->id);
        $statusRequest->assertForbidden();
    }

    public function test_view_own_status(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function test_view_foreign_public_status(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status = Status::factory([
            'user_id' => $foreignUser->id,
            'visibility' => StatusVisibility::PUBLIC,
        ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function test_view_foreign_followers_only_status_and_not_following(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status = Status::factory([
            'user_id' => $foreignUser->id,
            'visibility' => StatusVisibility::FOLLOWERS,
        ])->create();
        $this->assertFalse($user->can('view', $status));
    }

    public function test_view_foreign_followers_only_status_and_is_following(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        Follow::factory([
            'user_id' => $user->id,
            'follow_id' => $foreignUser->id,
        ])->create();
        $status = Status::factory([
            'user_id' => $foreignUser->id,
            'visibility' => StatusVisibility::FOLLOWERS,
        ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function test_view_foreign_private_status(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status = Status::factory([
            'user_id' => $foreignUser->id,
            'visibility' => StatusVisibility::PRIVATE,
        ])->create();
        $this->assertFalse($user->can('view', $status));
    }

    public function test_view_foreign_only_authenticated_users_status(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status = Status::factory([
            'user_id' => $foreignUser->id,
            'visibility' => StatusVisibility::AUTHENTICATED,
        ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function test_public_status_from_private_profile_is_not_displayed_on_events_page(): void
    {
        // create test scenario: Public Status with Event and Private Profile
        $event = Event::factory()->create();
        $checkin = Checkin::factory()->create();
        $checkin->status->update([
            'visibility' => StatusVisibility::PUBLIC,
            'event_id' => $event->id,
        ]);
        $checkin->user->update(['private_profile' => true]);

        // request statuses for event
        $response = $this->get("/api/v1/event/{$event->slug}/statuses");
        $response->assertOk();

        // check if status is not displayed
        $response->assertJsonCount(0, 'data');
    }

    public function test_unlisted_status_policy_is_working_correctly(): void
    {
        // create alice and bob
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        // create an unlisted status for bob
        $checkin = Checkin::factory(['user_id' => $bob->id])->create();
        $checkin->status->update(['visibility' => StatusVisibility::UNLISTED]);

        // alice follows bob
        FollowBackend::createOrRequestFollow($alice, $bob);

        // alice should not see the status on her (followers) dashboard
        $response = $this->get('/api/v1/dashboard');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        // alice should not see the status on active journeys
        $response = $this->get('/api/v1/statuses');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        // alice should see the status on bobs profile
        $response = $this->get("/api/v1/user/{$bob->username}/statuses");
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        // alice should see the status if queried directly
        $response = $this->get("/api/v1/status/{$checkin->status->id}");
        $response->assertOk();
        $response->assertJsonCount(1);
    }

    public function test_authenticated_status_is_not_visible_for_logged_out_users_on_event_page(): void
    {
        // create test scenario: Authenticated Status with Event
        $event = Event::factory()->create();
        $checkin = Checkin::factory()->create();
        $checkin->status->update([
            'visibility' => StatusVisibility::AUTHENTICATED,
            'event_id' => $event->id,
        ]);

        $this->assertGuest();

        $response = $this->get('/event/' . $event->slug);
        $response->assertOk();

        // check if status is not displayed
        $response->assertDontSee('/status/' . $checkin->status->id);
    }

    public function test_unauthenticated_view_trusted_status(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::TRUSTED])
            ->has(Checkin::factory())
            ->create();

        $this->assertGuest();
        $statusRequest = $this->getJson('/api/v1/status/' . $status->id);
        $statusRequest->assertForbidden();
    }

    public function test_view_foreign_trusted_status_and_not_trusted(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status = Status::factory([
            'user_id' => $foreignUser->id,
            'visibility' => StatusVisibility::TRUSTED,
        ])->create();
        $this->assertFalse($user->can('view', $status));
    }

    public function test_view_foreign_trusted_status_and_is_trusted(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();

        TrustedUser::create([
            'user_id' => $foreignUser->id,
            'trusted_id' => $user->id,
        ]);

        $status = Status::factory([
            'user_id' => $foreignUser->id,
            'visibility' => StatusVisibility::TRUSTED,
        ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    // Nötig?
    public function test_view_foreign_trusted_status_with_expired_trust(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();

        TrustedUser::create([
            'user_id' => $foreignUser->id,
            'trusted_id' => $user->id,
            'expires_at' => now()->subDay(),
        ]);

        $status = Status::factory([
            'user_id' => $foreignUser->id,
            'visibility' => StatusVisibility::TRUSTED,
        ])->create();
        $this->assertFalse($user->can('view', $status));
    }

    public function test_trusted_status_profile_visibility(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $checkin = Checkin::factory(['user_id' => $foreignUser->id])->create();
        $checkin->status->update(['visibility' => StatusVisibility::TRUSTED]);

        // Non-trusted user should not see trusted status on foreign user's profile
        $response = $this->get("/api/v1/user/{$foreignUser->username}/statuses");
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        TrustedUser::create([
            'user_id' => $foreignUser->id,
            'trusted_id' => $user->id,
        ]);

        $response = $this->get("/api/v1/user/{$foreignUser->username}/statuses");
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_trusted_status_dashboard_visibility(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        Passport::actingAs($user, ['*']);

        // Make foreignUser follow user so the status appears on their dashboard
        FollowBackend::createOrRequestFollow($user, $foreignUser);
        $user->refresh();

        $checkin = Checkin::factory(['user_id' => $foreignUser->id])->create();
        $checkin->status->update(['visibility' => StatusVisibility::TRUSTED]);

        // Non-trusted user should not see trusted status on their dashboard
        $response = $this->get('/api/v1/dashboard');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        TrustedUser::create([
            'user_id' => $foreignUser->id,
            'trusted_id' => $user->id,
        ]);

        // Trusted user should see trusted status on their dashboard
        $response = $this->get('/api/v1/dashboard');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }
}
