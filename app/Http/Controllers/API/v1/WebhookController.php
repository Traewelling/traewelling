<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\WebhookEventEnum;
use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\WebhookController as WebhookBackend;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class WebhookController extends Controller
{
    public function getWebhooks(Request $request): JsonResponse {
        $clientId = $request->user()->token()->client->id;
        $webhooks = Webhook::where('oauth_client_id', '=', $clientId)
                           ->where('user_id', '=', $request->user()->id)
                           ->get();
        return $this->sendResponse($webhooks);
    }

    public function createWebhook(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'events'   => ['required', 'array'],
                                            'events.*' => ['required', 'distinct', new Enum(WebhookEventEnum::class)],
                                            'url'      => ['required', 'url'],
                                        ]);
        $webhook   = WebhookBackend::createWebhook($request->user(), $request->user()->token()->client, $validated['url'], $validated['events']);
        $webhook->makeVisible('secret');
        return $this->sendResponse($webhook);
    }

    public function deleteWebhook(Request $request, int $webhookId): JsonResponse {
        try {
            WebhookBackend::deleteWebhook($request->user(), $request->user()->token()->client, $webhookId);
            return $this->sendResponse();
        } catch (PermissionException) {
            return $this->sendError('You are not allowed to delete this webhook', 403);
        } catch (ModelNotFoundException) {
            return $this->sendError('No webhook found for this id.');
        }
    }

    public function deleteAllWebhooks(Request $request): JsonResponse {
        WebhookBackend::deleteAllWebhooks($request->user(), $request->user()->token()->client);
        return $this->sendResponse();
    }
}
