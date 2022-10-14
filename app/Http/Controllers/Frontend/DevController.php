<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use http\Client;
use Illuminate\Contracts\Support\Renderable;
use Laravel\Passport\Http\Controllers\ClientController;
use Laravel\Passport\ClientRepository;

class DevController extends Controller
{
    public function renderAppList(): Renderable {
        $clients = new ClientRepository();

        $userId = request()->user()->getAuthIdentifier();

        return view('dev.apps', [
            'apps' => $clients->activeForUser($userId),
            //'apps' => SessionController::index(user: auth()->user()),
        ]);
    }

    public function renderAppUpdate(int $appId): Renderable {
        $clients = new ClientRepository();
        $app = $clients->findForUser($appId, auth()->user()->id);

        if (!$app) {
            abort(404);
        }
        return view('dev.apps-edit', [
            'title' => 'Anwendung bearbeiten', //ToDo Übersetzen
            'app' => $app,
        ]);
    }

    public function renderAppCreate(): Renderable {
        return view('dev.apps-edit', [
            'title' => 'Anwendung erstellen', //ToDo Übersetzen
        ]);
    }


}
