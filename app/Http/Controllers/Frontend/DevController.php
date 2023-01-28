<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use http\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Passport\ClientRepository;

class DevController extends Controller
{
    public function renderAppList(): View {
        $clients = new ClientRepository();

        $userId = request()->user()->getAuthIdentifier();

        return view('dev.apps', [
            'apps' => $clients->activeForUser($userId),
        ]);
    }

    public function renderUpdateApp(int $appId): View {
        $clients = new ClientRepository();
        $app     = $clients->findForUser($appId, auth()->user()->id);

        if (!$app) {
            abort(404);
        }
        return view('dev.apps-edit', [
            'title' => 'Anwendung bearbeiten', //ToDo Übersetzen
            'app'   => $app,
        ]);
    }

    public function renderCreateApp(): View {
        return view('dev.apps-edit', [
            'title' => 'Anwendung erstellen', //ToDo Übersetzen
            'app'   => null
        ]);
    }

    public function updateApp(int $appId, Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'name'     => ['required', 'string'],
                                            'redirect' => ['required', 'string'],
                                        ]);

        $clients = new ClientRepository();
        $app     = $clients->findForUser($appId, auth()->user()->id);

        if (!$app) {
            abort(404);
        }

        $clients->update($app, $validated['name'], $validated['redirect']);

        return redirect(route('dev.apps'))->with('success', __('settings.saved'));
    }

    public function createApp(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'name'         => ['required', 'string'],
                                            'redirect'     => ['required', 'string'],
                                            'confidential' => ['nullable'],
                                        ]);
        
        $clients = new ClientRepository();
        $clients->create(
            userId:   auth()->user()->id,
            name:     $validated['name'],
            redirect: $validated['redirect'],
            confidential: isset($validated['confidential'])
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
