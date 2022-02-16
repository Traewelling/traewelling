<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\AlreadyAcceptedException;
use App\Http\Controllers\Controller;
use App\Models\PrivacyAgreement;
use App\Models\User;
use Carbon\Carbon;

abstract class PrivacyPolicyController extends Controller
{

    public static function getCurrentPrivacyPolicy() {
        return PrivacyAgreement::where('valid_at', '<=', Carbon::now()->toIso8601String())
                                     ->orderByDesc('valid_at')
                                     ->first();
    }

    /**
     * @throws AlreadyAcceptedException
     */
    public static function acceptPrivacyPolicy(User $user): void {
        $privacyPolicy = self::getCurrentPrivacyPolicy();
        if ($privacyPolicy->valid_at->isBefore($user->privacy_ack_at)) {
            throw new AlreadyAcceptedException(agreement: $privacyPolicy, user: $user);
        }

        $user->update(['privacy_ack_at' => Carbon::now()->toIso8601String()]);
    }

}
