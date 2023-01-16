<?php

namespace App\Http\Controllers\Backend;

use App\Enum\WebhookEventEnum;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Models\Status;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Client;
use Spatie\WebhookServer\WebhookCall;

abstract class WebhookController extends Controller
{
    public static function createWebhook(
        User   $user,
        Client $client,
        string $url,
        array  $events,
    ): Webhook {
        $secret      = bin2hex(random_bytes(32));
        $webhook     = Webhook::create([
                                           'oauth_client_id' => $client->id,
                                           'url'             => $url,
                                           'secret'          => $secret,
                                           'user_id'         => $user->id
                                       ]);
        $events_data = [];
        foreach ($events as $event) {
            $events_data[] = [
                'event'      => $event,
                'webhook_id' => $webhook->id,
            ];
        }
        $webhook->events()->createMany($events_data);
        return $webhook;
    }

    /**
     * Deletes a webhook
     *
     * @param User   $user
     * @param Client $client
     * @param int    $webhookId
     *
     * @return bool
     * @throws PermissionException
     */
    public static function deleteWebhook(
        User   $user,
        Client $client,
        int    $webhookId
    ): bool {
        $webhook = Webhook::find($webhookId);

        if ($webhook === null) {
            throw new ModelNotFoundException();
        }
        if ($user->id != $webhook->user->id || $client->id != $webhook->oauthClient->id) {
            throw new PermissionException();
        }
        $webhook->delete();
        return true;
    }

    public static function sendStatusWebhook(Status $status) {
        $webhooks = Webhook::where('user_id', '=', $status->user_id)
                           ->get();
        foreach ($webhooks as $webhook) {
            Log::debug("Sending webhook", [
                'webhook_id' => $webhook->id,
                'user_id'    => $webhook->user->id,
            ]);
            $resource = new StatusResource($status);
            WebhookCall::create()
                       ->url($webhook->url)
                       ->withHeaders([
                                         'X-Trwl-User-Id'    => $status->user_id,
                                         'X-Trwl-Webhook-Id' => $webhook->id,
                                     ])
                       ->payload($resource->toArray(new Request()))
                       ->useSecret($webhook->secret)
                       ->dispatch();
        }
    }

    public static function deleteAllWebhooks(User $user, Client $client) {
        Webhook::where('user_id', '=', $user->id)
               ->where('oauth_client_id', '=', $client->id)
               ->delete();
    }
}
