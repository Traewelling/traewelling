<?php

namespace App\Http\Controllers\Backend;

use App\Enum\WebhookEvent;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserNotificationResource;
use App\Models\Status;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookCreationRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Client;
use Spatie\WebhookServer\WebhookCall;

abstract class WebhookController extends Controller
{
    public static function index(User $user): object
    {
        return $user->webhooks;
    }

    public static function createWebhook(
        WebhookCreationRequest $request
    ): Webhook {
        $secret      = bin2hex(random_bytes(32));
        $client = $request->client()->first();
        $user = $request->user()->first();
        $events = $request->events;
        $request->update(['revoked' => true]);
        $webhook     = Webhook::create([
            'oauth_client_id' => $client->id,
            'url'             => $request->url,
            'secret'          => $secret,
            'events'          => $events,
            'user_id'         => $user->id
        ]);

        Log::debug("Created a new webhook.", ['webhook' => $webhook]);

        return $webhook;
    }

    /**
     * Deletes a webhook
     *
     * @param User        $user
     * @param Client|null $client
     * @param int         $webhookId
     *
     * @return bool
     * @throws PermissionException
     */
    public static function deleteWebhook(
        User   $user,
        Client|null $client,
        int    $webhookId
    ): bool {
        $webhook = Webhook::find($webhookId);

        if ($webhook === null) {
            throw new ModelNotFoundException();
        }
        if ($client != null) {
            if ($user->id != $webhook->user->id || $client->id != $webhook->oauthClient->id) {
                throw new PermissionException();
            }
        }
        $webhook->delete();
        return true;
    }

    public static function sendStatusWebhook(Status $status, WebhookEvent $event)
    {
        self::dispatchWebhook($status->user, $event, [
            'status' => new StatusResource($status)
        ]);
    }

    public static function sendNotificationWebhook(User $user, Notification $notification)
    {
        self::dispatchWebhook($user, WebhookEvent::NOTIFICATION, [
            'notification' => new UserNotificationResource($notification)
        ]);
    }

    static function dispatchWebhook(User $user, WebhookEvent $event, array $data)
    {
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

    public static function deleteAllWebhooks(User $user, Client $client)
    {
        Webhook::where('user_id', '=', $user->id)
            ->where('oauth_client_id', '=', $client->id)
            ->delete();
    }

    /**
     * Creates a new webhook creation request
     */
    public static function createWebhookRequest(
        User $user,
        Client $client,
        string $oauth_code,
        string $url,
        int  $events,
    ): WebhookCreationRequest {
        return WebhookCreationRequest::create([
            'id' => hash('sha256', $oauth_code),
            'user_id' => $user->id,
            'oauth_client_id' => $client->id,
            'expires_at' => Carbon::now()->addHour(),
            'events' => $events,
            'url' => $url,
        ]);
    }

    public static function findWebhookRequest(
        string $oauth_code,
    ): WebhookCreationRequest | null {
        return WebhookCreationRequest::where('id', hash('sha256', $oauth_code))->first();
    }
}
