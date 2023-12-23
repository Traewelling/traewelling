<?php

namespace Tests\Feature\Webhooks;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\WebhookEvent;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\StatusController;
use App\Http\Resources\StatusResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Spatie\WebhookServer\CallWebhookJob;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class WebhookStatusTest extends TestCase
{
    use RefreshDatabase;

    public function testWebhookSendingOnStatusCreation() {
        Bus::fake();

        $user   = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_CREATE]);
        $status = $this->createStatus($user);

        Bus::assertDispatched(function(CallWebhookJob $job) use ($status) {
            assertEquals([
                             'event' => WebhookEvent::CHECKIN_CREATE->value,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       'status' => new StatusResource($status),
                         ], $job->payload);
            return true;
        });
    }

    public function testWebhookSendingOnStatusBodyChange() {
        Bus::fake();

        $user   = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        $this->actingAs($user)
             ->post(route('status.update'), [
                 'statusId'          => $status['id'],
                 'body'              => 'New Example Body',
                 'business_check'    => $status['business']->value,
                 'checkinVisibility' => $status['visibility']->value
             ]);

        Bus::assertDispatched(function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals('New Example Body', $job->payload['status']['body'],);
            return true;
        });
    }

    public function testWebhookSendingOnLike() {
        Bus::fake();

        $user   = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        StatusController::createLike($user, $status);

        // For self-likes, a CHECKIN_UPDATE is sent, but no notification.
        Bus::assertDispatched(function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals(1, count($job->payload['status']['likes']));
            return true;
        });
    }

    public function testWebhookSendingOnDestinationChange() {
        Bus::fake();

        $user   = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status    = $this->createStatus($user);
        $checkin   = $status->checkin()->first();
        $hafasTrip = TrainCheckinController::getHafasTrip(
            tripId:   self::TRIP_ID,
            lineName: self::ICE802['line']['name'],
            startId:  self::FRANKFURT_HBF['id']
        );
        $aachen    = $hafasTrip->stopovers->where('station.ibnr', self::AACHEN_HBF['id'])->first();
        TrainCheckinController::changeDestination($checkin, $aachen);

        Bus::assertDispatched(function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']->id);
            // This is really hacky, but I didn't get it working otherwise.
            $parsedStatus = json_decode($job->payload['status']->toJson());
            assertEquals(self::AACHEN_HBF['id'], $parsedStatus->train->destination->evaIdentifier);
            return true;
        });
    }

    public function testWebhookSendingOnBusinessChange() {
        Bus::fake();

        $user   = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        $this->actingAs($user)
             ->post(route('status.update'), [
                 'statusId'          => $status['id'],
                 'body'              => $status['body'],
                 'business_check'    => Business::BUSINESS->value,
                 'checkinVisibility' => $status['visibility']->value
             ]);

        Bus::assertDispatched(function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']->id);
            assertEquals(Business::BUSINESS, $job->payload['status']->business);
            return true;
        });
    }

    public function testWebhookSendingOnVisibilityChange() {
        Bus::fake();

        $user   = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        $this->actingAs($user)
             ->post(route('status.update'), [
                 'statusId'          => $status['id'],
                 'body'              => $status['body'],
                 'business_check'    => $status['business']->value,
                 'checkinVisibility' => StatusVisibility::UNLISTED->value,
             ]);

        Bus::assertDispatched(function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']->id);
            assertEquals(StatusVisibility::UNLISTED, $job->payload['status']->visibility);
            return true;
        });
    }

    public function testWebhookSendingOnStatusDeletion() {
        Bus::fake();

        $user   = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_DELETE]);
        $status = $this->createStatus($user);
        StatusController::DeleteStatus($user, $status['id']);

        Bus::assertDispatched(function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_DELETE->value,
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']->id);
            return true;
        });
    }

    protected function createStatus(User $user) {
        Http::fake([
                       '/locations*'                              => Http::response([self::FRANKFURT_HBF]),
                       '/trips/' . urlencode(self::TRIP_ID) . '*' => Http::response(self::TRIP_INFO),
                   ]);

        $trip = TrainCheckinController::getHafasTrip(
            tripId:   self::TRIP_ID,
            lineName: self::ICE802['line']['name'],
            startId:  self::FRANKFURT_HBF['id']
        );

        $origin      = HafasController::getStation(self::FRANKFURT_HBF['id']);
        $destination = HafasController::getStation(self::HANNOVER_HBF['id']);

        $checkin = TrainCheckinController::checkin(
            user:         $user,
            hafasTrip:    $trip,
            origin:       $origin,
            departure:    Carbon::parse(self::DEPARTURE_TIME),
            destination:  $destination,
            arrival:      Carbon::parse(self::ARRIVAL_TIME),
            travelReason: Business::PRIVATE,
            visibility:   StatusVisibility::PUBLIC,
            body:         self::EXAMPLE_BODY
        );
        return $checkin['status'];
    }
}
