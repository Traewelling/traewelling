<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class FrontendStaticController extends Controller
{
    public function changeLanguage($lang=NULL) {
        Session::put('language', $lang);
        return Redirect::back();
    }

    public function showFrontpage() {
        return view('welcome');
    }

    public function showImprint() {
        return view('imprint');
    }

    public function showAbout() {
        return view('about');
    }
}
