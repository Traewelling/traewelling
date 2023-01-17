<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\WebhookController as WebhookBackend;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public static function deleteWebhook(Request $request): RedirectResponse {
        $validated = $request->validate(['webhookId' => ['required', 'exists:webhooks,id']]);
        try {
            WebhookBackend::deleteWebhook(auth()->user(), null, $validated['webhookId']);
            return redirect()->route('settings')->with('alert-success', __('settings.delete-webhook.success'));
        } catch (PermissionException) {
            return redirect()->route('settings')->withErrors(__('messages.exception.general'));
        }
    }
}
