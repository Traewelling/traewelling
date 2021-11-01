<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class FrontendStaticController extends Controller
{
    public function renderLandingPage(): Renderable|RedirectResponse {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('welcome');
    }
}
