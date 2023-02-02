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
use Tests\WebhookTestCase;

use function PHPUnit\Framework\assertEquals;

class WebhookStatusTest extends WebhookTestCase
{
    use RefreshDatabase;

    public function testWebhookSendingOnStatusCreation()
    {
        Bus::fake();

        $user = $this->createGDPRAckedUser();
        $client = $this->createClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_CREATE]);
        $status = $this->createStatus($user);

        Bus::assertDispatched(function (CallWebhookJob $job) use ($status) {
            assertEquals([
                'event' => WebhookEvent::CHECKIN_CREATE->name(),
                'status' => new StatusResource($status),
            ], $job->payload);
            return true;
        });
    }

    public function testWebhookSendingOnStatusBodyChange()
    {
        Bus::fake();

        $user = $this->createGDPRAckedUser();
        $client = $this->createClient($user);
        $this->createWebhook($user, $client, [WebhookEvent::CHECKIN_UPDATE]);
        $status = $this->createStatus($user);
        $this->actingAs($user)
            ->post(route('status.update'), [
                'statusId' => $status['id'],
                'body' => 'New Example Body',
                'business_check' => $status['business']->value,
                'checkinVisibility' => $status['visibility']->value
            ]);

        Bus::assertDispatched(function (CallWebhookJob $job) use ($status) {
            assertEquals(
                WebhookEvent::CHECKIN_UPDATE->name(),
                $job->payload['event']
            );
            assertEquals($status->id, $job->payload['status']['id']);
            assertEquals('New Example Body', $job->payload['status']['body'],);
            return true;
        });
    }

    protected function createStatus(User $user)
    {
        Http::fake([
            '/locations*'                              => Http::response([self::FRANKFURT_HBF]),
            '/trips/' . urlencode(self::TRIP_ID) . '*' => Http::response(self::TRIP_INFO),
        ]);

        $trip = TrainCheckinController::getHafasTrip(
            tripId: self::TRIP_ID,
            lineName: self::ICE802['line']['name'],
            startId: self::FRANKFURT_HBF['id']
        );

        $origin = HafasController::getTrainStation(self::FRANKFURT_HBF['id']);
        $destination = HafasController::getTrainStation(self::HANNOVER_HBF['id']);

        $checkin = TrainCheckinController::checkin(
            user: $user,
            hafasTrip: $trip,
            origin: $origin,
            departure: Carbon::parse(self::DEPARTURE_TIME),
            destination: $destination,
            arrival: Carbon::parse(self::ARRIVAL_TIME),
            travelReason: Business::PRIVATE,
            visibility: StatusVisibility::PUBLIC,
            body: self::EXAMPLE_BODY
        );
        return $checkin['status'];
    }
}
