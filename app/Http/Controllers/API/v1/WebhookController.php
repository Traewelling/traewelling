<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\WebhookController as WebhookBackend;
use App\Http\Resources\WebhookResource;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function getWebhooks(Request $request): AnonymousResourceCollection
    {
        $clientId = $request->user()->token()->client->id;
        $webhooks = Webhook::where('oauth_client_id', '=', $clientId)
            ->where('user_id', '=', $request->user()->id)
            ->latest()
            ->simplePaginate(15);
        return WebhookResource::collection($webhooks);
    }

    public function getWebhook(Request $request, int $webhookId): WebhookResource|JsonResponse
    {
        $clientId = $request->user()->token()->client->id;
        $webhook = Webhook::where('oauth_client_id', '=', $clientId)
            ->where('user_id', '=', $request->user()->id)
            ->where('id', '=', $webhookId)
            ->first();
        if ($webhook == null) {
            return $this->sendError('No webhook found for this id.');
        }
        return new WebhookResource($webhook);
    }

    public function deleteWebhook(Request $request, int $webhookId): JsonResponse
    {
        try {
            WebhookBackend::deleteWebhook($request->user(), $request->user()->token()->client, $webhookId);
            return $this->sendResponse();
        } catch (PermissionException) {
            return $this->sendError('You are not allowed to delete this webhook', 403);
        } catch (ModelNotFoundException) {
            return $this->sendError('No webhook found for this id.');
        }
    }
}
