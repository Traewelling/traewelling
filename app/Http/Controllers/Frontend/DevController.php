<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use http\Client;
use Illuminate\Contracts\Support\Renderable;
use Laravel\Passport\Http\Controllers\ClientController;
use Laravel\Passport\ClientRepository;

class DevController extends Controller
{
    public function renderSettings(): Renderable {
        $clients = new ClientRepository();

        $userId = request()->user()->getAuthIdentifier();

        return view('dev.apps', [
            'apps' => $clients->activeForUser($userId),
            //'apps' => SessionController::index(user: auth()->user()),
        ]);
    }
}
