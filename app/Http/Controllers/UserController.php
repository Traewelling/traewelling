<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class UserController extends Controller
{
    public function updateProfile(Request $request) {
        $user = Auth::user();

        $user->username = $request->username;
        $user->name = $request->name;
        $user->save();
        return view('profile', compact('user'));
    }
}
