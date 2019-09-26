<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppStatusController extends Controller
{
    public function appStatus() {

        return view('appstatus');
    }
}
