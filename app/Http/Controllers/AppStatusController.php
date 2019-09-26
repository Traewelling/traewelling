<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class AppStatusController extends Controller
{
    public function appStatus() {

        $users = User::count();
        $users_last_week = User::where('created_at', '<', 'NOW() - INTERVAL 1 WEEK')->count();

        return view('appstatus', [
            'users' => $users,
            'users_last_week' => $users_last_week,
        ]);
    }
}
