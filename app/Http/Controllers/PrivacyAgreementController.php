<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Backend\PrivacyPolicyController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class PrivacyAgreementController extends Controller
{

    public function intercept(): Renderable {
        $agreement = PrivacyPolicyController::getCurrentPrivacyPolicy();
        $user      = Auth::user();

        return view('legal.privacy-interception', ['agreement' => $agreement, 'user' => $user]);
    }

    public function ack(Request $request): RedirectResponse|JsonResponse {
        PrivacyPolicyController::acceptPrivacyPolicy(user: auth()->user());

        if ($request->is('api*')) {
            return response()->json(['message' => 'privacy agreement successfully accepted'], 202);
        }

        return redirect()->route('dashboard');
    }
}
