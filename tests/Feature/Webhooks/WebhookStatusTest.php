<?php

namespace Tests\Feature\Webhooks;

use App\Dto\Internal\CheckInRequestDto;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\WebhookEvent;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\StatusController;
use App\Jobs\MonitoredCallWebhookJob;
use App\Models\User;
use App\Repositories\CheckinHydratorRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

use function PHPUnit\Framework\assertEquals;

use Tests\ApiTestCase;

class WebhookStatusTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_webhook_sending_on_status_creation(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_CREATE]);
        $status = $this->createStatus($user);

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(WebhookEvent::CHECKIN_CREATE->value, $job->payload['event']);
            assertEquals($status->id, $job->payload['status']['id']);

            return true;
        });
    }

    public function test_webhook_sending_on_status_body_change()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        $this->actingAs($user)
            ->post(route('status.update'), [
                'statusId' => $status['id'],
                'body' => 'New Example Body',
                'business_check' => $status['business']->value,
                'checkinVisibility' => $status['visibility']->value,
            ]);

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals('New Example Body', $job->payload['status']['body']);

            return true;
        });
    }

    public function test_webhook_sending_on_like()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        StatusController::createLike($user, $status);

        // For self-likes, a CHECKIN_UPDATE is sent, but no notification.
        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals(1, $job->payload['status']['likes']);

            return true;
        });
    }

    public function test_webhook_sending_on_destination_change()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        $checkin = $status->checkin()->first();
        $trip = app(CheckinHydratorRepository::class)->getHafasTrip(
            tripID: self::TRIP_ID,
            lineName: self::ICE802['line']['name']
        );
        $trip->load('stopovers.station');
        $goettingen = $trip->stopovers->first(fn ($s) => $s->station->name === self::GOETTINGEN_HBF['name']);
        TrainCheckinController::changeDestination($checkin, $goettingen);

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals(self::GOETTINGEN_HBF['name'], $job->payload['status']['checkin']['destination']['name']);

            return true;
        });
    }

    public function test_webhook_sending_on_business_change()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        $this->actingAs($user)
            ->post(route('status.update'), [
                'statusId' => $status['id'],
                'body' => $status['body'],
                'business_check' => Business::BUSINESS->value,
                'checkinVisibility' => $status['visibility']->value,
            ]);

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals(Business::BUSINESS->value, $job->payload['status']['business']);

            return true;
        });
    }

    public function test_webhook_sending_on_visibility_change()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        $this->actingAs($user)
            ->post(route('status.update'), [
                'statusId' => $status['id'],
                'body' => $status['body'],
                'business_check' => $status['business']->value,
                'checkinVisibility' => StatusVisibility::UNLISTED->value,
            ]);

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals(StatusVisibility::UNLISTED->value, $job->payload['status']['visibility']);

            return true;
        });
    }

    public function test_webhook_sending_on_status_deletion()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_DELETE]);
        $status = $this->createStatus($user);
        StatusController::DeleteStatus($user, $status['id']);

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_DELETE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);

            return true;
        });
    }

    public function test_webhook_sending_on_status_update_via_api()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);

        $this->actAsApiUserWithAllScopes($user);
        $this->putJson("/api/v1/status/{$status->id}", [
            'body' => 'Updated via API',
            'business' => Business::BUSINESS->value,
            'visibility' => StatusVisibility::PRIVATE->value,
        ])
            ->assertSuccessful();

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals('Updated via API', $job->payload['status']['body']);
            assertEquals(Business::BUSINESS->value, $job->payload['status']['business']);
            assertEquals(StatusVisibility::PRIVATE->value, $job->payload['status']['visibility']);

            return true;
        });
    }

    public function test_webhook_sending_on_tag_creation()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);

        $this->actAsApiUserWithAllScopes($user);
        $this->postJson("/api/v1/status/{$status->id}/tags", [
            'key' => 'trwl:seat',
            'value' => '42',
            'visibility' => StatusVisibility::PUBLIC->value,
        ])
            ->assertSuccessful();

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);

            return true;
        });
    }

    public function test_webhook_sending_on_tag_update()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);

        // Create a tag first
        $this->actAsApiUserWithAllScopes($user);
        $this->postJson("/api/v1/status/{$status->id}/tags", [
            'key' => 'trwl:seat',
            'value' => '42',
            'visibility' => StatusVisibility::PUBLIC->value,
        ])
            ->assertSuccessful();

        Bus::fake(); // Reset to only check the update

        // Update the tag
        $this->actAsApiUserWithAllScopes($user);
        $this->putJson("/api/v1/status/{$status->id}/tags/trwl:seat", [
            'value' => '43',
            'visibility' => StatusVisibility::PRIVATE->value,
        ])
            ->assertSuccessful();

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);

            return true;
        });
    }

    public function test_webhook_sending_on_tag_deletion()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);

        // Create a tag first
        $this->actAsApiUserWithAllScopes($user);
        $this->postJson("/api/v1/status/{$status->id}/tags", [
            'key' => 'trwl:seat',
            'value' => '42',
            'visibility' => StatusVisibility::PUBLIC->value,
        ])
            ->assertSuccessful();

        Bus::fake(); // Reset to only check the deletion

        // Delete the tag
        $this->actAsApiUserWithAllScopes($user);
        $this->deleteJson("/api/v1/status/{$status->id}/tags/trwl:seat")
            ->assertSuccessful();

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);

            return true;
        });
    }

    public function test_webhook_sending_on_like_removal()
    {
        Bus::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);

        // Create a like first
        StatusController::createLike($user, $status);

        Bus::fake(); // Reset to only check the unlike

        // Remove the like
        StatusController::destroyLike($user, $status->id);

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            // 'likes' is an integer count in the API response, not an array
            assertEquals(0, $job->payload['status']['likes']);

            return true;
        });
    }

    protected function createStatus(User $user)
    {
        // Create stations in database to avoid API calls
        $origin = \App\Models\Station::factory()->create([
            'name' => self::FRANKFURT_HBF['name'],
            'latitude' => self::FRANKFURT_HBF['location']['latitude'],
            'longitude' => self::FRANKFURT_HBF['location']['longitude'],
        ]);

        $destination = \App\Models\Station::factory()->create([
            'name' => self::HANNOVER_HBF['name'],
            'latitude' => self::HANNOVER_HBF['location']['latitude'],
            'longitude' => self::HANNOVER_HBF['location']['longitude'],
        ]);

        Http::fake([
            'api.transitous.org/api/v5/trip*' => Http::response(self::MOTIS_TRIP_RESPONSE),
            '/locations*' => Http::response([self::FRANKFURT_HBF]),
            '/trips/' . urlencode(self::TRIP_ID) . '*' => Http::response(self::TRIP_INFO),
        ]);

        $trip = app(CheckinHydratorRepository::class)->getHafasTrip(
            tripID: self::TRIP_ID,
            lineName: self::ICE802['line']['name']
        );

        $dto = new CheckInRequestDto();
        $dto->setUser($user)
            ->setTrip($trip)
            ->setOrigin($origin)
            ->setDeparture(Carbon::parse(self::DEPARTURE_TIME))
            ->setDestination($destination)
            ->setArrival(Carbon::parse(self::ARRIVAL_TIME))
            ->setTravelReason(Business::PRIVATE)
            ->setStatusVisibility(StatusVisibility::PUBLIC)
            ->setBody(self::EXAMPLE_BODY);
        $checkin = TrainCheckinController::checkin($dto);

        return $checkin->status;
    }
}
