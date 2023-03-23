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

    public function testWebhookSendingOnStatusCreation(): void {
        Bus::fake();

        $user   = $this->createGDPRAckedUser();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_CREATE]);
        $status = $this->createStatus($user);

        Bus::assertDispatched(function(CallWebhookJob $job) use ($status) {
            assertEquals([
                             'event' => WebhookEvent::CHECKIN_CREATE->name(),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       'status' => new StatusResource($status),
                         ], $job->payload);
            return true;
        });
    }

    public function testWebhookSendingOnStatusBodyChange(): void {
        Bus::fake();

        $user   = $this->createGDPRAckedUser();
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

        Bus::assertDispatched(static function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->name(),
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals('New Example Body', $job->payload['status']['body'],);
            return true;
        });
    }

    public function testWebhookSendingOnLike(): void {
        Bus::fake();

        $user   = $this->createGDPRAckedUser();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        StatusController::createLike($user, $status);

        // For self-likes, a CHECKIN_UPDATE is sent, but no notification.
        Bus::assertDispatched(function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->name(),
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals(1, count($job->payload['status']['likes']));
            return true;
        });
    }

    public function testWebhookSendingOnDestinationChange(): void {
        Bus::fake();

        $user   = $this->createGDPRAckedUser();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status    = $this->createStatus($user);
        $checkin   = $status->trainCheckin()->first();
        $hafasTrip = TrainCheckinController::getHafasTrip(
            tripId:   self::TRIP_ID,
            lineName: self::ICE802['line']['name'],
            startId:  self::FRANKFURT_HBF['id']
        );
        $aachen    = $hafasTrip->stopoversNew->where('trainStation.ibnr', self::AACHEN_HBF['id'])->first();
        TrainCheckinController::changeDestination($checkin, $aachen);

        Bus::assertDispatched(static function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->name(),
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']->id);
            // This is really hacky, but i didn't got it working otherwise.
            $parsedStatus = json_decode($job->payload['status']->toJson());
            assertEquals(self::AACHEN_HBF['id'], $parsedStatus->train->destination->evaIdentifier);
            return true;
        });
    }

    public function testWebhookSendingOnBusinessChange(): void {
        Bus::fake();

        $user   = $this->createGDPRAckedUser();
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

        Bus::assertDispatched(static function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->name(),
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']->id);
            assertEquals(Business::BUSINESS, $job->payload['status']->business);
            return true;
        });
    }

    public function testWebhookSendingOnVisibilityChange(): void {
        Bus::fake();

        $user   = $this->createGDPRAckedUser();
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

        Bus::assertDispatched(static function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->name(),
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']->id);
            assertEquals(StatusVisibility::UNLISTED, $job->payload['status']->visibility);
            return true;
        });
    }

    public function testWebhookSendingOnStatusDeletion(): void {
        Bus::fake();

        $user   = $this->createGDPRAckedUser();
        $client = $this->createWebhookClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_DELETE]);
        $status = $this->createStatus($user);
        StatusController::DeleteStatus($user, $status['id']);

        Bus::assertDispatched(static function(CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_DELETE->name(),
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

        $origin      = HafasController::getTrainStation(self::FRANKFURT_HBF['id']);
        $destination = HafasController::getTrainStation(self::HANNOVER_HBF['id']);

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
