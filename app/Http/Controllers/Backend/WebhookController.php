<?php

namespace App\Http\Controllers\Backend;

use App\Enum\WebhookEvent as WebhookEventEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserNotificationResource;
use App\Models\OAuthClient;
use App\Models\Status;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookCreationRequest;
use App\Models\WebhookEvent;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookServer\WebhookCall;

abstract class WebhookController extends Controller
{
    public static function index(User $user): object {
        return $user->webhooks;
    }

    public static function createWebhook(
        WebhookCreationRequest $request
    ): Webhook {
        DB::beginTransaction();
        $request->delete();
        $secret = bin2hex(random_bytes(32));
        $client = $request->client()->first();
        $user   = $request->user()->first();

        $webhook = Webhook::create([
                                       'oauth_client_id' => $client->id,
                                       'url'             => $request->url,
                                       'secret'          => $secret,
                                       'user_id'         => $user->id
                                   ]);

        foreach (explode(",", $request->events) as $event) {
            WebhookEvent::create([
                                     'webhook_id' => $webhook->id,
                                     'event'      => $event,
                                 ]);
        }

        DB::commit();

        Log::debug("Created a new webhook.", ['webhook' => $webhook]);

        return $webhook;
    }

    /**
     * Deletes a webhook
     *
     * @throws AuthorizationException
     */
    public static function deleteWebhook(
        Webhook          $webhook,
        OAuthClient|null $client
    ): bool {
        Gate::authorize("delete", $webhook);
        // Checking if the client is allowed to delete here,
        // because I found no way of doing that in the policy.
        if ($client != null && $client->id != $webhook->client->id) {
            throw new AuthorizationException();
        }
        $webhook->delete();
        return true;
    }

    public static function sendStatusWebhook(Status $status, WebhookEventEnum $event): void {
        self::dispatchWebhook($status->user, $event, [
            'status' => new StatusResource($status)
        ]);
    }

    public static function sendNotificationWebhook(User $user, DatabaseNotification $notification): void {
        self::dispatchWebhook($user, WebhookEventEnum::NOTIFICATION, [
            'notification' => new UserNotificationResource($notification)
        ]);
    }

    public static function dispatchWebhook(User $user, WebhookEventEnum $event, array $data): void {
        if (!config("trwl.webhooks_active")) {
            return;
        }

        $webhooks = $user->webhooks()
                         ->withWhereHas('events', function($builder) use ($event) {
                             $builder->where('event', '=', $event);
                         })
                         ->where('user_id', $user->id)
                         ->get();

        foreach ($webhooks as $webhook) {
            Log::debug("Sending webhook", [
                'webhook_id' => $webhook->id,
                'user_id'    => $webhook->user->id,
            ]);
            WebhookCall::create()
                       ->url($webhook->url)
                       ->withHeaders([
                                         'X-Trwl-User-Id'    => $user->id,
                                         'X-Trwl-Webhook-Id' => $webhook->id,
                                     ])
                       ->payload([
                                     'event' => $event->value,
                                     ...$data
                                 ])
                       ->useSecret($webhook->secret)
                       ->dispatch();
        }
    }

    /**
     * Creates a new webhook creation request
     */
    public static function createWebhookRequest(
        User        $user,
        OAuthClient $client,
        string      $oauthCode,
        string      $url,
        array       $events,
    ): WebhookCreationRequest {
        return WebhookCreationRequest::create([
                                                  'id'              => hash('sha256', $oauthCode),
                                                  'user_id'         => $user->id,
                                                  'oauth_client_id' => $client->id,
                                                  'expires_at'      => Carbon::now()->addHour(),
                                                  'events'          => implode(",", $events),
                                                  'url'             => $url,
                                              ]);
    }

    public static function findWebhookRequest(
        string $oauthCode
    ): WebhookCreationRequest|null {
        return WebhookCreationRequest::where('id', hash('sha256', $oauthCode))->first();
    }
}
