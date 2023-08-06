<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\OAuthClientRepository;
use App\Rules\SecureUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Passport\ClientRepository;

class DevController extends Controller
{
    public function renderAppList(): View {
        $clients = new ClientRepository();

        $userId = auth()->user()->getAuthIdentifier();

        return view('dev.apps', [
            'apps' => $clients->activeForUser($userId)
        ]);
    }

    public function createPersonalAccessToken(): RedirectResponse {
        $token = auth()->user()->createToken('PAT@' . auth()->user()->username, ['*']);
        $token->token->update(['expires_at' => now()->addMonths(3)]);
        return back()->with('token', $token->accessToken);
    }

    public function renderUpdateApp(int $appId): View {
        $clients = new ClientRepository();
        $app     = $clients->findForUser($appId, auth()->user()->id);

        if (!$app) {
            abort(404);
        }
        return view('dev.apps-edit', [
            'app' => $app,
        ]);
    }

    public function renderCreateApp(): View {
        return view('dev.apps-edit', [
            'app' => null
        ]);
    }

    public function updateApp(int $appId, Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'name'                   => ['required', 'string'],
                                            'redirect'               => ['required', 'string'],
                                            'confidential'           => ['nullable'],
                                            'enable_webhooks'        => ['nullable'],
                                            'authorized_webhook_url' => ['nullable', 'url', new SecureUrl()],
                                            'privacy_policy_url'     => ['nullable', 'url', new SecureUrl()],
                                        ]);

        $clients = new OAuthClientRepository();
        $app     = $clients->findForUser($appId, auth()->user()->id);

        $clients->update(
            $app,
            $validated['name'],
            $validated['redirect'],
            $validated['privacy_policy_url'],
            isset($validated['enable_webhooks']) || $clients->hasWebhooks(id: $appId),
            $validated['authorized_webhook_url'],
            isset($validated['confidential'])
        );

        return redirect(route('dev.apps'))->with('success', __('settings.saved'));
    }

    public function createApp(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'name'                   => ['required', 'string'],
                                            'redirect'               => ['required', 'string'],
                                            'confidential'           => ['nullable'],
                                            'enable_webhooks'        => ['nullable'],
                                            'authorized_webhook_url' => ['nullable', 'url', new SecureUrl()],
                                            'privacy_policy_url'     => ['nullable', 'url', new SecureUrl()],
                                        ]);

        $clients = new OAuthClientRepository();
        $clients->create(
            userId:               auth()->user()->id,
            name:                 $validated['name'],
            redirect:             $validated['redirect'],
            confidential: isset($validated['confidential']),
            privacyPolicyUrl:     $validated['privacy_policy_url'],
            webhooksEnabled: isset($validated['enable_webhooks']),
            authorizedWebhookUrl: $validated['authorized_webhook_url'],
        );

        return redirect(route('dev.apps'))->with('success', __('settings.saved'));
    }

    public function destroyApp(int $appId): RedirectResponse {
        $clients = new ClientRepository();
        $app     = $clients->findForUser($appId, auth()->user()->id);

        if (!$app) {
            abort(404);
        }
        $clients->delete($app);

        return redirect(route('dev.apps'))->with('success', __('settings.saved'));
    }
}
