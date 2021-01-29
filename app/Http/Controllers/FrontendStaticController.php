<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class FrontendStaticController extends Controller
{
    public function changeLanguage($lang = null): RedirectResponse {
        Session::put('language', $lang);
        return Redirect::back();
    }

    public function renderLandingPage(): Renderable|RedirectResponse {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('welcome');
    }
}
