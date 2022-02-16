<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PrivacyAgreement;
use Carbon\Carbon;

abstract class PrivacyPolicyController extends Controller
{

    public static function getCurrentPrivacyPolicy() {
        return PrivacyAgreement::where('valid_at', '<=', Carbon::now()->toIso8601String())
                                     ->orderByDesc('valid_at')
                                     ->first();
    }

    public static function acceptPrivacyPolicy(User $user):void {
        $user->update(['privacy_ack_at' => Carbon::now()->toIso8601String()]);
    }

}
