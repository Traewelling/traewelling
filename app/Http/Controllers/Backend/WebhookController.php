<?php

namespace App\Http\Controllers\Backend;

use App\Enum\WebhookEvent;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserNotificationResource;
use App\Models\OAuthClient;
use App\Models\Status;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookCreationRequest;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Client;
use Spatie\WebhookServer\WebhookCall;

abstract class WebhookController extends Controller {
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
        $user = $request->user()->first();
        $events = $request->events;
        $webhook = Webhook::create([
            'oauth_client_id' => $client->id,
            'url'             => $request->url,
            'secret'          => $secret,
            'events'          => $events,
            'user_id'         => $user->id
        ]);
        DB::commit();

        Log::debug("Created a new webhook.", ['webhook' => $webhook]);

        return $webhook;
    }

    /**
     * Deletes a webhook
     *
     * @throws PermissionException
     */
    public static function deleteWebhook(
        Webhook $webhook,
        OAuthClient|null $client
    ): bool {
        Gate::authorize("delete", $webhook);
        // Checking if the client is allowed to delete here,
        // because i found no way of doing that in the policy.
        if ($client != null && $client->id != $webhook->client->id) {
            throw new PermissionException();
        }
        $webhook->delete();
        return true;
    }

    public static function sendStatusWebhook(Status $status, WebhookEvent $event): void {
        self::dispatchWebhook($status->user, $event, [
            'status' => new StatusResource($status)
        ]);
    }

    public static function sendNotificationWebhook(User $user, DatabaseNotification $notification): void {
        self::dispatchWebhook($user, WebhookEvent::NOTIFICATION, [
            'notification' => new UserNotificationResource($notification)
        ]);
    }

    static function dispatchWebhook(User $user, WebhookEvent $event, array $data): void {
        $webhooks = $user->webhooks()
            ->whereBitflag('events', $event->value)
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
                    'event' => $event->name(),
                    ...$data
                ])
                ->useSecret($webhook->secret)
                ->dispatch();
        }
    }

    public static function deleteAllWebhooks(User $user, Client $client): void {
        Webhook::where('user_id', '=', $user->id)
            ->where('oauth_client_id', '=', $client->id)
            ->delete();
    }

    /**
     * Creates a new webhook creation request
     */
    public static function createWebhookRequest(
        User $user,
        OAuthClient $client,
        string $oauthCode,
        string $url,
        int  $events,
    ): WebhookCreationRequest {
        return WebhookCreationRequest::create([
            'id' => hash('sha256', $oauthCode),
            'user_id' => $user->id,
            'oauth_client_id' => $client->id,
            'expires_at' => Carbon::now()->addHour(),
            'events' => $events,
            'url' => $url,
        ]);
    }

    public static function findWebhookRequest(
        string $oauthCode
    ): WebhookCreationRequest|null {
        return WebhookCreationRequest::where('id', hash('sha256', $oauthCode))->first();
    }
}
