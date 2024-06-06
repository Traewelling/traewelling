<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\WebhookController as WebhookBackend;
use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function deleteWebhook(Request $request): RedirectResponse {
        $validated = $request->validate(['webhookId' => ['required', 'exists:webhooks,id']]);
        try {
            $webhook = Webhook::find($validated['webhookId']);
            WebhookBackend::deleteWebhook($webhook, null);
            return redirect()->route('settings')->with('alert-success', __('settings.delete-webhook.success'));
        } catch (AuthorizationException) {
            return redirect()->route('settings')->withErrors(__('messages.exception.general'));
        }
    }
}
