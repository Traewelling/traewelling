<?php

namespace App\Http\Controllers\API\v1;


use App\Exceptions\UserAlreadyMutedException;
use App\Exceptions\UserNotMutedException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\UserController as BackendUserBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WebhookResource;
use App\Models\User;
use App\Models\Webhook;
use Error;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class WebhookController extends ResponseController
{

    //TODO: How to save the webhook in relation to the requester? access_token is not good to catch. We don't have implemented laravel passport correctly so we cannot use the client.

    public function listWebhooks(Request $request): JsonResponse {
        $webhooks = Webhook::where('user_id', auth()->user()->id)
                           ->where('access_token_id', '') //TODO
                           ->get();

        return $this->sendv1Response(WebhookResource::collection($webhooks));
    }

    public function createWebhook(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'url' => ['required', 'url'],
                                        ]);

        if (!str_starts_with($validated['url'], 'https://')) {
            return $this->sendv1Error('URL must start with https://', 400);
        }

        $alreadyExist = Webhook::where('user_id', auth()->user()->id)
                               ->where('access_token_id', '') //TODO
                               ->where('url', $validated['url'])
                               ->exists();

        if ($alreadyExist) {
            return $this->sendv1Error('Webhook already exists', 400);
        }

        $webhook = Webhook::create([
                                       'external_id'     => Str::uuid(),
                                       'user_id'         => auth()->user()->id,
                                       'url'             => $validated['url'],
                                       'access_token_id' => '', //TODO
                                   ]);

        return $this->sendv1Response(new WebhookResource($webhook));
    }

    public function deleteWebhook(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'id' => ['required', 'uuid'],
                                        ]);

        $webhook = Webhook::where('external_id', $validated['id'])
                          ->where('access_token_id', '') //TODO
                          ->first();

        if ($webhook === null) {
            return $this->sendv1Error('Webhook not found.');
        }

        $webhook->delete();
        return $this->sendv1Response(['success' => true]);
    }
}
