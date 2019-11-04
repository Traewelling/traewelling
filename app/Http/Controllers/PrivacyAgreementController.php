<?php

namespace App\Http\Controllers;

use App\PrivacyAgreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivacyAgreementController extends Controller
{

    public function intercept() {
        $agreement = PrivacyAgreement::where('valid_at', '<=', date("Y-m-d H:i:s"))->orderByDesc('valid_at')->take(1)->first();
        $user = Auth::user();

        return view('privacy-interception', ['agreement' => $agreement, 'user' => $user]);
    }

    public function ack() {
        $user = Auth::user();
        $user->privacy_ack_at = now();
        $user->save();

        return redirect("/");
    }
}
