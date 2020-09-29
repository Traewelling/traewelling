<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class FrontendStaticController extends Controller
{
    public function changeLanguage($lang = null) {
        Session::put('language', $lang);
        return Redirect::back();
    }
}
