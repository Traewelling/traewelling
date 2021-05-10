<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FrontendStaticController extends Controller
{
    public function renderLandingPage(): Renderable|RedirectResponse {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('welcome');
    }
}
